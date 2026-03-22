<?php

/*
 * This file is part of Refine
 *
 * Copyright(c) 2024 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefinePasswordNoneHidden;

use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    /**
     * @var string コピー元MYページ/会員登録内容変更(入力ページ)テンプレートファイル
     */
    private $originMypageChangeTemplate;

    /**
     * @var string コピー元MYページ/ログインテンプレートファイル
     */
    private $originMypageLoginTemplate;

    /**
     * @var string コピー元商品購入ログインテンプレートファイル
     */
    private $originShoppingLoginTemplate;

    /**
     * @var string コピー元アイコンファイル
     */
    private $originIcon;

    /**
     * @var string MYページ/会員登録内容変更(入力ページ)テンプレートファイル名
     */
    private $mypageChangeTemplateFileName = 'Mypage/change.twig';

    /**
     * @var string MYページ/ログインテンプレートファイル名
     */
    private $mypageLoginTemplateFileName = 'Mypage/login.twig';

    /**
     * @var string MYページ/会員登録内容変更(入力ページ)テンプレートファイル名
     */
    private $shoppingLoginTemplateFileName = 'Shopping/login.twig';

    /**
     * @var string アイコンファイル名
     */
    private $iconFileName = 'assets/img/common/icon_eye.png';

    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        // コピー元MYページ/会員登録内容変更(入力ページ)テンプレートファイル
        $this->originMypageChangeTemplate = __DIR__.'/Resource/template/default/'.$this->mypageChangeTemplateFileName;
        // コピー元MYページ/ログインテンプレートファイル
        $this->originMypageLoginTemplate = __DIR__.'/Resource/template/default/'.$this->mypageLoginTemplateFileName;
        // コピー元商品購入ログインテンプレートファイル
        $this->originShoppingLoginTemplate = __DIR__.'/Resource/template/default/'.$this->shoppingLoginTemplateFileName;
        // コピー元アイコンファイル
        $this->originIcon = __DIR__.'/Resource/template/default/'.$this->iconFileName;
    }

    /**
     * @param array $meta
     * @param ContainerInterface $container
     * @throws \Exception
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
    }

    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     * @throws \Exception
     */
    public function enable(array $meta = null, ContainerInterface $container)
    {
        $this->copyTemplate($container);
    }

    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     * @throws \Exception
     */
    public function disable(array $meta = null, ContainerInterface $container)
    {
        // メールテンプレートの削除
        $this->removeTemplate($container);
    }

    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     */
    public function update(array $meta = null, ContainerInterface $container)
    {
    }

    /**
     * テンプレートのコピー
     *
     * @param ContainerInterface $container
     */
    private function copyTemplate(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        // ファイルコピー
        $file = new Filesystem();

        // コピー元MYページ/会員登録内容変更(入力ページ)テンプレートファイルをコピー
        $file->copy($this->originMypageChangeTemplate, $templateDir.'/'.$this->mypageChangeTemplateFileName);
        // コピー元MYページ/ログインテンプレートファイルをコピー
        $file->copy($this->originMypageLoginTemplate, $templateDir.'/'.$this->mypageLoginTemplateFileName);
        // コピー元商品購入ログインテンプレートファイルをコピー
        $file->copy($this->originShoppingLoginTemplate, $templateDir.'/'.$this->shoppingLoginTemplateFileName);
        // コピー元アイコンファイルをコピー
        $file->copy($this->originIcon, $container->getParameter('kernel.project_dir').'/html/template/default/'.$this->iconFileName);
    }

    /**
     * メールテンプレートの削除
     *
     * @param ContainerInterface $container
     */
    private function removeTemplate(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        $file = new Filesystem();
        $file->remove($templateDir.'/'.$this->mypageChangeTemplateFileName);
        $file->remove($templateDir.'/'.$this->mypageLoginTemplateFileName);
        $file->remove($templateDir.'/'.$this->shoppingLoginTemplateFileName);
        $file->remove($container->getParameter('kernel.project_dir').'/html/template/default/'.$this->iconFileName);
    }
}
