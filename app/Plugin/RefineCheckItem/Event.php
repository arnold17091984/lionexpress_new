<?php

namespace Plugin\RefineCheckItem;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Common\EccubeConfig;
use Eccube\Request\Context;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class Event implements EventSubscriberInterface
{
    /**
     * Cookie 名称
     */
    const COOKIE_NAME = 'eccube_product_history';

    /**
     * Cookie 保存件数
     */
    const MAX_SAVE_NUM = 10;

    /**
     * Cookie 保存日数
     */
    const MAX_SAVE_DAY = 30;

    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param EccubeConfig $eccubeConfig
     * @param Context $context
     */
    public function __construct(
        EccubeConfig $eccubeConfig,
        Context $context
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    /**
     * 商品詳細ページアクセス時に商品IDをCookieに保存する
     *
     * @param FilterResponseEvent $event
     * @throws \Exception
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest() || $this->context->isAdmin())
        {
            return;
        }

        if ($event->getRequest()->get('_route') !== 'product_detail')
        {
            return;
        }

        $productId = $event->getRequest()->get('id');
        if (!is_null($productId))
        {
            $this->setCookie($productId, $event);
        }
    }

    /**
     * Cookie の取得
     *
     * @param KernelEvent $event
     * @return array|mixed
     */
    private function getProductIdsFromCookie(KernelEvent $event)
    {
        $cookie = $event->getRequest()->cookies->get(self::COOKIE_NAME);

        return json_decode($cookie, true) ?? [];
    }

    /**
     * Cookieに商品IDを追加
     *
     * @param $productId
     * @param FilterResponseEvent $event
     */
    private function setCookie($productId, FilterResponseEvent $event)
    {
        $productIds = (new ArrayCollection($this->getProductIdsFromCookie($event)))->toArray();
        $key = array_search($productId, $productIds);
        if (false !== $key)
        {
            array_splice($productIds, $key, 1);
        }
        $productIds[] = $productId;

        if (self::MAX_SAVE_NUM < count($productIds))
        {
            array_splice($productIds, 0, count($productIds) - self::MAX_SAVE_NUM);
        }

        $cookie = new Cookie(
            self::COOKIE_NAME,
            json_encode($productIds),
            (new \DateTime())->modify(self::MAX_SAVE_DAY.' day'),
            $this->eccubeConfig['env(ECCUBE_COOKIE_PATH)']
        );

        $response = $event->getResponse();
        $response->headers->setCookie($cookie);
        $event->setResponse($response);
    }
}
