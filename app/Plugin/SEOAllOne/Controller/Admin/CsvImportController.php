<?php

namespace Plugin\SEOAllOne\Controller\Admin;

use Eccube\Common\Constant;
use Eccube\Controller\Admin\AbstractCsvImportController;
use Plugin\SEOAllOne\Form\Type\Admin\CsvImportType;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Util\CacheUtil;
use Eccube\Util\StringUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Plugin\SEOAllOne\Entity\SEOAllOneProduct;
use Plugin\SEOAllOne\Entity\SEOAllOneCategory;
use Plugin\SEOAllOne\Repository\SEOAllOneProductRepository;
use Plugin\SEOAllOne\Repository\SEOAllOneCategoryRepository;
use Symfony\Component\Filesystem\Filesystem;
use Eccube\Service\CsvImportService;
use Eccube\Stream\Filter\ConvertLineFeedFilter;
use Eccube\Stream\Filter\SjisToUtf8EncodingFilter;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CsvImportController extends AbstractCsvImportController
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    private $errors = [];

    protected $isSplitCsv = false;

    protected $csvFileNo = 1;

    protected $currentLineNo = 1;

    /**
     * CsvImportController constructor.
     *
     * @param CategoryRepository $categoryRepository
     *
     * @throws \Exception
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        SEOAllOneCategoryRepository $SEOAllOneCategoryRepository,
        SEOAllOneProductRepository $SEOAllOneProductRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->SEOAllOneCategoryRepository = $SEOAllOneCategoryRepository;
        $this->SEOAllOneProductRepository = $SEOAllOneProductRepository;
    }



    /**
     * 登録、更新時のエラー画面表示
     */
    protected function addErrors($message)
    {
        $this->errors[] = $message;
    }

    /**
     * @return array
     */
    protected function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    protected function hasErrors()
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * 登録、更新時のエラー画面表示
     *
     * @param FormInterface $form
     * @param array $headers
     * @param bool $rollback
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function renderWithError($form, $headers, $rollback = true)
    {
        if ($this->hasErrors()) {
            if ($rollback) {
                $this->entityManager->getConnection()->rollback();
            }
        }

        $this->removeUploadedFile();

        if ($this->isSplitCsv) {
            return $this->json([
                'success' => !$this->hasErrors(),
                'success_message' => trans('admin.common.csv_upload_line_success', [
                    '%from%' => $this->convertLineNo(2),
                    '%to%' => $this->currentLineNo, ]),
                'errors' => $this->errors,
                'error_message' => trans('admin.common.csv_upload_line_error', [
                    '%from%' => $this->convertLineNo(2), ]),
            ]);
        }

        return [
            'form' => $form->createView(),
            'headers' => $headers,
            'errors' => $this->errors,
        ];
    }

        /**
     * SEOカテゴリCSVヘッダー定義
     */
    protected function getCategoryCsvHeader()
    {
        return [
            'カテゴリID' => [
                'id' => 'category_id',
                'description' => 'カテゴリID',
                'required' => true,
            ],
            'カテゴリ名' => [
                'id' => 'category_name',
                'description' => 'カテゴリ名',
                'required' => true,
            ],
            'Title' => [
                'id' => 'title',
                'description' => 'タイトル',
                'required' => false,
            ],
            'Description' => [
                'id' => 'description',
                'description' => '説明',
                'required' => false,
            ],
            'Keyword' => [
                'id' => 'keyword',
                'description' => 'キーワード',
                'required' => false,
            ],
            'Author' => [
                'id' => 'author',
                'description' => 'Author',
                'required' => false,
            ],
            'Canonical' => [
                'id' => 'canonical',
                'description' => 'Canonical',
                'required' => false,
            ],
            'og_title' => [
                'id' => 'og_title',
                'description' => 'og_title',
                'required' => false,
            ],
            'og_description' => [
                'id' => 'og_description',
                'description' => 'og_description',
                'required' => false,
            ],
            'og_type' => [
                'id' => 'og_type',
                'description' => 'og_type',
                'required' => false,
            ],
            'og_url' => [
                'id' => 'og_url',
                'description' => 'og_url',
                'required' => false,
            ],
        ];
    }

    /**
     * SEO商品CSVヘッダー定義
     */
    protected function getProductCsvHeader()
    {
        return [
            '商品ID' => [
                'id' => 'product_id',
                'description' => '商品ID',
                'required' => true,
            ],
            '商品名' => [
                'id' => 'product_name',
                'description' => '商品名',
                'required' => true,
            ],
            'Title' => [
                'id' => 'title',
                'description' => 'タイトル',
                'required' => false,
            ],
            'Description' => [
                'id' => 'description',
                'description' => '説明',
                'required' => false,
            ],
            'Keyword' => [
                'id' => 'keyword',
                'description' => 'キーワード',
                'required' => false,
            ],
            'Author' => [
                'id' => 'author',
                'description' => 'Author',
                'required' => false,
            ],
            'Canonical' => [
                'id' => 'canonical',
                'description' => 'Canonical',
                'required' => false,
            ],
            'og_title' => [
                'id' => 'og_title',
                'description' => 'og_title',
                'required' => false,
            ],
            'og_description' => [
                'id' => 'og_description',
                'description' => 'og_description',
                'required' => false,
            ],
            'og_type' => [
                'id' => 'og_type',
                'description' => 'og_type',
                'required' => false,
            ],
            'og_url' => [
                'id' => 'og_url',
                'description' => 'og_url',
                'required' => false,
            ],
            // 'noindex設定' => [
            //     'id' => 'noindex_flg',
            //     'description' => 'noindex設定',
            //     'required' => false,
            // ],
            // 'リダイレクトURL' => [
            //     'id' => 'redirect_url',
            //     'description' => 'リダイレクトURL',
            //     'required' => false,
            // ],
        ];
    }

    protected function getCsvTempFiles()
    {
        $files = Finder::create()
            ->in($this->eccubeConfig['eccube_csv_temp_realdir'])
            ->name('*.csv')
            ->files();

        $choices = [];
        foreach ($files as $file) {
            $choices[$file->getBaseName()] = $file->getRealPath();
        }

        return $choices;
    }

    /**
     * SEOカテゴリ登録CSVアップロード
     *
     * @Route("/%eccube_admin_route%/seoallone/seo_category_csv_upload", name="seoallone_category_csv_import", methods={"POST", "GET"})
     * @Template("@SEOAllOne/admin/import/category.twig")
     */
    public function importCategory(Request $request, CacheUtil $cacheUtil)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();
        $headers = $this->getCategoryCsvHeader();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->isSplitCsv = $form['is_split_csv']->getData();
                $this->csvFileNo = $form['csv_file_no']->getData();
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    log_info('SEOカテゴリCSV登録開始');
                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $getId = function ($item) {
                        return $item['id'];
                    };
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
                        return $value['required'];
                    })));

                    $columnHeaders = $data->getColumnHeaders();

                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));

                        return $this->renderWithError($form, $headers, false);
                    }
                    $headerSize = count($columnHeaders);
                    $headerByKey = array_flip(array_map($getId, $headers));

                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    // CSVファイルの登録処理
                    foreach ($data as $row) {
                        $line = $this->convertLineNo($data->key() + 1);
                        $this->currentLineNo = $line;
                        if ($headerSize != count($row)) {
                            $message = trans('admin.common.csv_invalid_format_line', ['%line%' => $line]);
                            $this->addErrors($message);
                            return $this->renderWithError($form, $headers);
                        }

                        $Category = null;
                        if (isset($row[$headerByKey['category_id']]) && StringUtil::isNotBlank($row[$headerByKey['category_id']])) {
                            if (!preg_match('/^\d+$/', $row[$headerByKey['category_id']])) {
                                $this->addErrors($line.'行目のカテゴリIDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }

                            /** @var $Category Category */
                            $Category = $this->categoryRepository->find($row[$headerByKey['category_id']]);
                            if (!$Category) {
                                $this->addErrors($line.'行目のカテゴリIDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                            else{
                                if (isset($row[$headerByKey['category_name']]) && StringUtil::isNotBlank($row[$headerByKey['category_name']])) {
                                    if (strcmp($Category->getName(), $row[$headerByKey['category_name']])  !== 0) {
                                        $Category->setName($row[$headerByKey['category_name']]);
                                        $this->categoryRepository->save($Category);
                                    }
                                }
                                else{
                                    $this->addErrors($line.'行目のカテゴリ名は空白にできません。');

                                    return $this->renderWithError($form, $headers);
                                }
                            }
                        }
                        else{
                            $this->addErrors($line.'行目のカテゴリIDが存在しません。');

                            return $this->renderWithError($form, $headers);
                        }

                        $SEOAllOneCategory = $this->SEOAllOneCategoryRepository->findOneBy(['Category' => $row[$headerByKey['category_id']]]);
                        if (!$SEOAllOneCategory) {
                            /** @var $SEOAllOneCategory SEOAllOneCategory */
                            $SEOAllOneCategory = new SEOAllOneCategory();
                        }
                        
                        if (isset($row[$headerByKey['title']]) && StringUtil::isNotBlank($row[$headerByKey['title']])) {
                            $SEOAllOneCategory->setTitle($row[$headerByKey['title']]);
                        }
                        else{
                            $SEOAllOneCategory->setTitle(NULL);
                        }

                        if (isset($row[$headerByKey['description']]) && StringUtil::isNotBlank($row[$headerByKey['description']])) {
                            $SEOAllOneCategory->setDescription($row[$headerByKey['description']]);
                        }
                        else{
                            $SEOAllOneCategory->setDescription(NULL);
                        }

                        if (isset($row[$headerByKey['keyword']]) && StringUtil::isNotBlank($row[$headerByKey['keyword']])) {
                            $SEOAllOneCategory->setKeyword($row[$headerByKey['keyword']]);
                        }
                        else{
                            $SEOAllOneCategory->setKeyword(NULL);
                        }

                        if (isset($row[$headerByKey['author']]) && StringUtil::isNotBlank($row[$headerByKey['author']])) {
                            $SEOAllOneCategory->setAuthor($row[$headerByKey['author']]);
                        }
                        else{
                            $SEOAllOneCategory->setAuthor(NULL);
                        }

                        if (isset($row[$headerByKey['canonical']]) && StringUtil::isNotBlank($row[$headerByKey['canonical']])) {
                            $SEOAllOneCategory->setCanonical($row[$headerByKey['canonical']]);
                        }
                        else{
                            $SEOAllOneCategory->setCanonical(NULL);
                        }

                        if (isset($row[$headerByKey['og_title']]) && StringUtil::isNotBlank($row[$headerByKey['og_title']])) {
                            $SEOAllOneCategory->setOGTitle($row[$headerByKey['og_title']]);
                        }
                        else{
                            $SEOAllOneCategory->setOGTitle(NULL);
                        }

                        if (isset($row[$headerByKey['og_description']]) && StringUtil::isNotBlank($row[$headerByKey['og_description']])) {
                            $SEOAllOneCategory->setOGDescription($row[$headerByKey['og_description']]);
                        }
                        else{
                            $SEOAllOneCategory->setOGDescription(NULL);
                        }

                        if (isset($row[$headerByKey['og_type']]) && StringUtil::isNotBlank($row[$headerByKey['og_type']])) {
                            if (!in_array($row[$headerByKey['og_type']], ['website', 'product', 'article', 'blog'])) {
                                $this->addErrors($line."行目のエラー: ogタイプは、website、product、article、blogのみを受け付けます。");

                                return $this->renderWithError($form, $headers);
                            }

                            $SEOAllOneCategory->setOGType($row[$headerByKey['og_type']]);
                        }
                        else{
                            $SEOAllOneCategory->setOGType(NULL);
                        }

                        if (isset($row[$headerByKey['og_url']]) && StringUtil::isNotBlank($row[$headerByKey['og_url']])) {
                            $SEOAllOneCategory->setOGUrl($row[$headerByKey['og_url']]);
                        }
                        else{
                            $SEOAllOneCategory->setOGUrl(NULL);
                        }

                        $SEOAllOneCategory->setCategory($Category);

                        $SEOAllOneCategory->setDelFlg('0');

                        if ($this->hasErrors()) {
                            return $this->renderWithError($form, $headers);
                        }
                        $this->entityManager->persist($SEOAllOneCategory);
                        $this->SEOAllOneCategoryRepository->save($SEOAllOneCategory);
                    }

                    $this->entityManager->getConnection()->commit();
                    log_info('カテゴリCSV登録完了');

                    if (!$this->isSplitCsv) {
                        $message = 'admin.common.csv_upload_complete';
                        $this->session->getFlashBag()->add('eccube.admin.success', $message);
                    }

                    $cacheUtil->clearDoctrineCache();
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * SEOカテゴリ登録CSVアップロード
     *
     * @Route("/%eccube_admin_route%/seoallone/seo_product_csv_upload", name="seoallone_product_csv_import", methods={"POST", "GET"})
     * @Template("@SEOAllOne/admin/import/product.twig")
     */
    public function importProduct(Request $request, CacheUtil $cacheUtil)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();
        $headers = $this->getProductCsvHeader();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->isSplitCsv = $form['is_split_csv']->getData();
                $this->csvFileNo = $form['csv_file_no']->getData();
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    log_info('SEOカテゴリCSV登録開始');
                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $getId = function ($item) {
                        return $item['id'];
                    };
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
                        return $value['required'];
                    })));

                    $columnHeaders = $data->getColumnHeaders();

                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $headerSize = count($columnHeaders);
                    $headerByKey = array_flip(array_map($getId, $headers));

                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    // CSVファイルの登録処理
                    foreach ($data as $row) {
                        $line = $this->convertLineNo($data->key() + 1);
                        $this->currentLineNo = $line;
                        if ($headerSize != count($row)) {
                            $message = trans('admin.common.csv_invalid_format_line', ['%line%' => $line]);
                            $this->addErrors($message);
                            return $this->renderWithError($form, $headers);
                        }

                        $Product = null;
                        if (isset($row[$headerByKey['product_id']]) && StringUtil::isNotBlank($row[$headerByKey['product_id']])) {
                            if (!preg_match('/^\d+$/', $row[$headerByKey['product_id']])) {
                                $this->addErrors($line.'行目の商品IDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }

                            /** @var $Product Product */
                            $Product = $this->productRepository->find($row[$headerByKey['product_id']]);
                            if (!$Product) {
                                $this->addErrors($line.'行目の商品IDが存在しません。');

                                return $this->renderWithError($form, $headers);
                            }
                            else{
                                if (isset($row[$headerByKey['product_name']]) && StringUtil::isNotBlank($row[$headerByKey['product_name']])) {
                                    if (strcmp($Product->getName(), $row[$headerByKey['product_name']]) !== 0 ) {
                                        $Product->setName($row[$headerByKey['product_name']]);
                                        $this->productRepository->save($Product);
                                    }
                                }
                                else{
                                    $this->addErrors($line.'行目の商品名は空白にできません。');

                                    return $this->renderWithError($form, $headers);
                                }
                            }
                        }
                        else{
                            $this->addErrors($line.'行目の商品IDが存在しません。');

                            return $this->renderWithError($form, $headers);
                        }

                        $SEOAllOneProduct = $this->SEOAllOneProductRepository->findOneBy(['Product' => $row[$headerByKey['product_id']]]);
                        if (!$SEOAllOneProduct) {
                            /** @var $SEOAllOneProduct SEOAllOneProduct */
                            $SEOAllOneProduct = new SEOAllOneProduct();

                            $SEOAllOneProduct->setUpdatedFlg('0');
                        }
                        
                        if (isset($row[$headerByKey['title']]) && StringUtil::isNotBlank($row[$headerByKey['title']])) {
                            $SEOAllOneProduct->setTitle($row[$headerByKey['title']]);
                        }
                        else{
                            $SEOAllOneProduct->setTitle(NULL);
                        }

                        if (isset($row[$headerByKey['description']]) && StringUtil::isNotBlank($row[$headerByKey['description']])) {
                            $SEOAllOneProduct->setDescription($row[$headerByKey['description']]);
                        }
                        else{
                            $SEOAllOneProduct->setDescription(NULL);
                        }

                        if (isset($row[$headerByKey['keyword']]) && StringUtil::isNotBlank($row[$headerByKey['keyword']])) {
                            $SEOAllOneProduct->setKeyword($row[$headerByKey['keyword']]);
                        }
                        else{
                            $SEOAllOneProduct->setKeyword(NULL);
                        }

                        if (isset($row[$headerByKey['author']]) && StringUtil::isNotBlank($row[$headerByKey['author']])) {
                            $SEOAllOneProduct->setAuthor($row[$headerByKey['author']]);
                        }
                        else{
                            $SEOAllOneProduct->setAuthor(NULL);
                        }

                        if (isset($row[$headerByKey['canonical']]) && StringUtil::isNotBlank($row[$headerByKey['canonical']])) {
                            $SEOAllOneProduct->setCanonical($row[$headerByKey['canonical']]);
                        }
                        else{
                            $SEOAllOneProduct->setCanonical(NULL);
                        }

                        if (isset($row[$headerByKey['og_title']]) && StringUtil::isNotBlank($row[$headerByKey['og_title']])) {
                            $SEOAllOneProduct->setOGTitle($row[$headerByKey['og_title']]);
                        }
                        else{
                            $SEOAllOneProduct->setOGTitle(NULL);
                        }

                        if (isset($row[$headerByKey['og_description']]) && StringUtil::isNotBlank($row[$headerByKey['og_description']])) {
                            $SEOAllOneProduct->setOGDescription($row[$headerByKey['og_description']]);
                        }
                        else{
                            $SEOAllOneProduct->setOGDescription(NULL);
                        }

                        if (isset($row[$headerByKey['og_type']]) && StringUtil::isNotBlank($row[$headerByKey['og_type']])) {
                            if (!in_array($row[$headerByKey['og_type']], ['website', 'product', 'article', 'blog'])) {
                                $this->addErrors($line."行目のエラー: ogタイプは、website、product、article、blogのみを受け付けます。");

                                return $this->renderWithError($form, $headers);
                            }

                            $SEOAllOneProduct->setOGType($row[$headerByKey['og_type']]);
                        }
                        else{
                            $SEOAllOneProduct->setOGType(NULL);
                        }

                        if (isset($row[$headerByKey['og_url']]) && StringUtil::isNotBlank($row[$headerByKey['og_url']])) {
                            $SEOAllOneProduct->setOGUrl($row[$headerByKey['og_url']]);
                        }
                        else{
                            $SEOAllOneProduct->setOGUrl(NULL);
                        }

                        // if (isset($row[$headerByKey['noindex_flg']]) && StringUtil::isNotBlank($row[$headerByKey['noindex_flg']])) {
                        //     if (!preg_match('/^[01]$/', $row[$headerByKey['noindex_flg']])) {
                        //         $this->addErrors($line.'行エラー: noindex設定は、0または1のみを受け付けます。');
    
                        //         return $this->renderWithError($form, $headers);
                        //     }

                        //     $SEOAllOneProduct->setNoindexFlg($row[$headerByKey['noindex_flg']]);
                        // }
                        // else{
                        //     $SEOAllOneProduct->setNoindexFlg(0);
                        // }

                        // if (isset($row[$headerByKey['redirect_url']]) && StringUtil::isNotBlank($row[$headerByKey['redirect_url']])) {
                        //     $SEOAllOneProduct->setRedirectUrl($row[$headerByKey['redirect_url']]);
                        // }
                        // else{
                        //     $SEOAllOneProduct->setRedirectUrl(NULL);
                        // }

                        $SEOAllOneProduct->setProduct($Product);

                        $SEOAllOneProduct->setDelFlg('0');

                        if ($this->hasErrors()) {
                            return $this->renderWithError($form, $headers);
                        }
                        $this->entityManager->persist($SEOAllOneProduct);
                        $this->SEOAllOneProductRepository->save($SEOAllOneProduct);
                    }

                    $this->entityManager->getConnection()->commit();
                    log_info('カテゴリCSV登録完了');

                    if (!$this->isSplitCsv) {
                        $message = 'admin.common.csv_upload_complete';
                        $this->session->getFlashBag()->add('eccube.admin.success', $message);
                    }

                    $cacheUtil->clearDoctrineCache();
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/product/csv_split", name="seoallone_product_csv_split", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function splitCsv(Request $request)
    {
        $this->isTokenValid();

        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dir = $this->eccubeConfig['eccube_csv_temp_realdir'];
            if (!file_exists($dir)) {
                $fs = new Filesystem();
                $fs->mkdir($dir);
            }

            $data = $form['import_file']->getData();
            $src = new \SplFileObject($data->getRealPath());
            $src->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY);

            $fileNo = 1;
            $fileName = StringUtil::random(8);

            $dist = new \SplFileObject($dir.'/'.$fileName.$fileNo.'.csv', 'w');
            $header = $src->current();
            $src->next();
            $dist->fputcsv($header);

            $i = 0;
            while ($row = $src->current()) {
                $dist->fputcsv($row);
                $src->next();

                if (!$src->eof() && ++$i % $this->eccubeConfig['eccube_csv_split_lines'] === 0) {
                    $fileNo++;
                    $dist = new \SplFileObject($dir.'/'.$fileName.$fileNo.'.csv', 'w');
                    $dist->fputcsv($header);
                }
            }

            return $this->json(['success' => true, 'file_name' => $fileName, 'max_file_no' => $fileNo]);
        }

        return $this->json(['success' => false, 'message' => $form->getErrors(true, true)]);
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/product/csv_split_import", name="seoallone_product_csv_split_import", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function importProductCsv(Request $request, CsrfTokenManagerInterface $tokenManager)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $choices = $this->getCsvTempFiles();
        $filename = $request->get('file_name');
        if (!isset($choices[$filename])) {
            throw new BadRequestHttpException();
        }
        $path = $this->eccubeConfig['eccube_csv_temp_realdir'].'/'.$filename;
        $request->files->set('admin_csv_import', ['import_file' => new UploadedFile(
            $path,
            'import.csv',
            'text/csv',
            null,
            true
        )]);
        $request->setMethod('POST');
        $request->request->set('admin_csv_import', [
            Constant::TOKEN_NAME => $tokenManager->getToken('admin_csv_import')->getValue(),
            'is_split_csv' => true,
            'csv_file_no' => $request->get('file_no'),
        ]);
        return $this->forwardToRoute('seoallone_product_csv_import');
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/category/csv_split_import", name="seoallone_category_csv_split_import", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function importCategoryCsv(Request $request, CsrfTokenManagerInterface $tokenManager)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $choices = $this->getCsvTempFiles();
        $filename = $request->get('file_name');
        if (!isset($choices[$filename])) {
            throw new BadRequestHttpException();
        }
        $path = $this->eccubeConfig['eccube_csv_temp_realdir'].'/'.$filename;
        $request->files->set('admin_csv_import', ['import_file' => new UploadedFile(
            $path,
            'import.csv',
            'text/csv',
            null,
            true
        )]);
        $request->setMethod('POST');
        $request->request->set('admin_csv_import', [
            Constant::TOKEN_NAME => $tokenManager->getToken('admin_csv_import')->getValue(),
            'is_split_csv' => true,
            'csv_file_no' => $request->get('file_no'),
        ]);
        return $this->forwardToRoute('seoallone_category_csv_import');
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/product/csv_split_cleanup", name="seoallone_product_csv_split_cleanup", methods={"POST"})
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function cleanupSplitCsv(Request $request)
    {
        $this->isTokenValid();
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $files = $request->get('files', []);
        $choices = $this->getCsvTempFiles();
        foreach ($files as $filename) {
            if (isset($choices[$filename])) {
                unlink($choices[$filename]);
            } else {
                return $this->json(['success' => false]);
            }
        }
        return $this->json(['success' => true]);
    }

    protected function convertLineNo($currentLineNo)
    {
        if ($this->isSplitCsv) {
            return ($this->eccubeConfig['eccube_csv_split_lines']) * ($this->csvFileNo - 1) + $currentLineNo;
        }
        return $currentLineNo;
    }
}
