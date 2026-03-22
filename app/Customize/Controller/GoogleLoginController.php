<?php

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\CustomerStatusRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class GoogleLoginController extends AbstractController
{
    /** @var CustomerRepository */
    protected $customerRepository;

    /** @var CustomerStatusRepository */
    protected $customerStatusRepository;

    /** @var EncoderFactoryInterface */
    protected $encoderFactory;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(
        CustomerRepository $customerRepository,
        CustomerStatusRepository $customerStatusRepository,
        EncoderFactoryInterface $encoderFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerStatusRepository = $customerStatusRepository;
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/google/login", name="google_login", methods={"GET"})
     */
    public function redirectToGoogle(Request $request)
    {
        $clientId = $this->getEnvVar('GOOGLE_CLIENT_ID');
        $redirectUri = $this->getEnvVar('GOOGLE_REDIRECT_URI');

        if (empty($clientId) || empty($redirectUri)) {
            $this->addFlash('eccube.front.request.error', 'Google認証の設定が不完全です。');
            return $this->redirectToRoute('mypage_login');
        }

        $state = bin2hex(random_bytes(16));
        $request->getSession()->set('google_oauth_state', $state);

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return new RedirectResponse('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    /**
     * @Route("/google/callback", name="google_callback", methods={"GET"})
     */
    public function handleCallback(Request $request)
    {
        $code = $request->query->get('code');
        $state = $request->query->get('state');
        $error = $request->query->get('error');
        $sessionState = $request->getSession()->get('google_oauth_state');

        if ($error) {
            $this->addFlash('eccube.front.request.error', 'Googleログインがキャンセルされました。');
            return $this->redirectToRoute('mypage_login');
        }

        // Timing-safe state comparison
        if (empty($state) || empty($sessionState) || !hash_equals($sessionState, $state)) {
            $this->addFlash('eccube.front.request.error', 'セキュリティトークンが無効です。もう一度お試しください。');
            return $this->redirectToRoute('mypage_login');
        }

        $request->getSession()->remove('google_oauth_state');

        if (empty($code)) {
            $this->addFlash('eccube.front.request.error', '認証コードが取得できませんでした。');
            return $this->redirectToRoute('mypage_login');
        }

        // Exchange code for access token
        $tokenData = $this->exchangeCodeForToken($code);
        if (!$tokenData || !isset($tokenData['access_token'])) {
            log_error('Google OAuth token exchange failed', ['response' => $tokenData]);
            $this->addFlash('eccube.front.request.error', 'アクセストークンの取得に失敗しました。');
            return $this->redirectToRoute('mypage_login');
        }

        // Get user info from Google
        $googleUser = $this->getGoogleUserInfo($tokenData['access_token']);
        if (!$googleUser || !isset($googleUser['email'])) {
            log_error('Google OAuth userinfo failed', ['response' => $googleUser]);
            $this->addFlash('eccube.front.request.error', 'Googleアカウント情報の取得に失敗しました。');
            return $this->redirectToRoute('mypage_login');
        }

        // Verify email is confirmed by Google
        if (empty($googleUser['verified_email'])) {
            $this->addFlash('eccube.front.request.error', 'Googleアカウントのメールアドレスが確認されていません。');
            return $this->redirectToRoute('mypage_login');
        }

        $email = $googleUser['email'];

        // Find existing customer by email (any active status)
        $CustomerStatus = $this->customerStatusRepository->find(CustomerStatus::REGULAR);
        $customer = $this->customerRepository->findOneBy([
            'email' => $email,
            'Status' => $CustomerStatus,
        ]);

        if (!$customer) {
            // Check if email exists with provisional status
            $provisional = $this->customerRepository->findOneBy(['email' => $email]);
            if ($provisional) {
                // Upgrade provisional to regular
                $provisional->setStatus($CustomerStatus);
                $this->entityManager->flush();
                $customer = $provisional;
                log_info('Google OAuth upgraded provisional customer', ['email' => $email]);
            } else {
                // Auto-create new customer
                try {
                    $customer = $this->createCustomerFromGoogle($googleUser, $CustomerStatus);
                } catch (\Exception $e) {
                    log_error('Google OAuth registration failed', [
                        'email' => $email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $this->addFlash('eccube.front.request.error', 'アカウントの作成に失敗しました。');
                    return $this->redirectToRoute('mypage_login');
                }
            }
        }

        // Programmatic login
        $token = new UsernamePasswordToken($customer, null, 'customer', $customer->getRoles());
        $this->tokenStorage->setToken($token);
        $request->getSession()->set('_security_customer', serialize($token));
        $request->getSession()->migrate(true);

        log_info('Google OAuth login success', ['email' => $email, 'customer_id' => $customer->getId()]);

        return $this->redirectToRoute('mypage');
    }

    /**
     * Create a new Customer from Google profile data
     */
    private function createCustomerFromGoogle(array $googleUser, CustomerStatus $CustomerStatus)
    {
        $Customer = new Customer();

        /** @var \Eccube\Security\Core\Encoder\PasswordEncoder $encoder */
        $encoder = $this->encoderFactory->getEncoder($Customer);
        $salt = $encoder->createSalt();
        $randomPassword = bin2hex(random_bytes(20));
        $encodedPassword = $encoder->encodePassword($randomPassword, $salt);
        $secretKey = $this->customerRepository->getUniqueSecretKey();

        $familyName = mb_substr(
            (isset($googleUser['family_name']) && $googleUser['family_name'] !== '')
                ? $googleUser['family_name'] : 'Google',
            0, 255
        );
        $givenName = mb_substr(
            (isset($googleUser['given_name']) && $googleUser['given_name'] !== '')
                ? $googleUser['given_name'] : explode('@', $googleUser['email'])[0],
            0, 255
        );

        $Customer
            ->setName01($familyName)
            ->setName02($givenName)
            ->setKana01('グーグル')
            ->setKana02('ユーザー')
            ->setEmail($googleUser['email'])
            ->setSalt($salt)
            ->setPassword($encodedPassword)
            ->setSecretKey($secretKey)
            ->setStatus($CustomerStatus)
            ->setPoint(0);

        $this->entityManager->persist($Customer);
        $this->entityManager->flush();

        log_info('Google OAuth auto-registration', ['email' => $googleUser['email']]);

        return $Customer;
    }

    /**
     * Exchange authorization code for access token
     */
    private function exchangeCodeForToken($code)
    {
        $postFields = http_build_query([
            'code' => $code,
            'client_id' => $this->getEnvVar('GOOGLE_CLIENT_ID'),
            'client_secret' => $this->getEnvVar('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => $this->getEnvVar('GOOGLE_REDIRECT_URI'),
            'grant_type' => 'authorization_code',
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_error('Google OAuth cURL error (token)', ['error' => $curlError]);
            return null;
        }

        if ($httpCode !== 200 || !$response) {
            log_error('Google OAuth token error', ['http_code' => $httpCode, 'response' => $response]);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Get user profile from Google
     */
    private function getGoogleUserInfo($accessToken)
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            log_error('Google OAuth cURL error (userinfo)', ['error' => $curlError]);
            return null;
        }

        if ($httpCode !== 200 || !$response) {
            log_error('Google OAuth userinfo error', ['http_code' => $httpCode, 'response' => $response]);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Read environment variable with fallback
     */
    private function getEnvVar($key)
    {
        return $_ENV[$key] ?? (getenv($key) ?: '');
    }
}
