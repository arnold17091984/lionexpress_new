<?php
/*
 * This file is part of Refine
 *
 * Copyright(c) 2022 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineTemplateEditor\Service;

use phpDocumentor\Reflection\Types\Static_;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Constraints\Uuid;

class TemplateService
{
    /**@var SessionInterface */
    protected $session;
    const SESSION_KEY_PREFIX = 'REFINE_PLUGIN_TEMPLATE_EDITOR';

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    /**
     * セッション情報初期化処理
     * @return mixed
     */
    public function sessionInitialize()
    {
        $this->session->remove(self::SESSION_KEY_PREFIX);
        $this->session->set(self::SESSION_KEY_PREFIX, []);
    }

    /**
     * セッションキーを生成し返します.
     * @param $category
     * @return string
     */
    public function generateSessionKey($category): string
    {
        $counter = 0;
        $session = $this->session->get(self::SESSION_KEY_PREFIX);
        while (true) {
            $counter++;
            $key = sprintf('%s.%d', $category, $counter);

            if (array_key_exists($key, $session)) {
                // キーがすでに存在する場合はスキップ
                continue;
            }
            //
            break;
        }
        return $key;
    }

    /**
     * 指定されたディレクトリ内のtwigを検索し
     * キー：フルパス、値：捜査ディレクトリ以降のパスを含むファイル名の形式のリストで結果を返します.
     * @param $dir
     * @return array
     */
    public function getTemplates($dir): array
    {
        $finder = new Finder();
        $finder->files()->in($dir)->name('*.twig');
        $templates = [];

        foreach ($finder as $file) {
            if ($file->getRelativePath() !== '') {
                $name = sprintf('%s/%s', $file->getRelativePath(), $file->getFilename());
            } else {
                $name = $file->getFilename();
            }
            $item = [
                'path' => $file->getPathname(),
                'name' => $name,

            ];
            $templates[] = $item;
        }
        return $templates;
    }

    /**
     * templateの配列にセッションキーを設定します.
     * @param $category
     * @param array $templates
     * @return array
     */
    public function setSessionTemplateCode($category, array $templates = []): array
    {
        foreach ($templates as &$template) {
            $sessionKey = $this->generateSessionKey($category);
            $template['code'] = $sessionKey;
            $this->putSession($sessionKey, $template);
        }
        return $templates;
    }

    public function putSession($key, $value)
    {
        $session = $this->session->get(self::SESSION_KEY_PREFIX);
        $session[$key] = $value;
        $this->session->set(self::SESSION_KEY_PREFIX, $session);
    }

    /**
     * templateの情報を返します.
     * @param $key
     * @return array|null
     */
    public function getTemplateInfoFromSession($key): ?array
    {

        if (!$this->session->has(self::SESSION_KEY_PREFIX)) {
            return null;
        }

        $session = $this->session->get(self::SESSION_KEY_PREFIX);
        if (!$session || !isset($session[$key])) {
            return null;
        }

        $template = $session[$key];
        if (!isset($template['name']) || $template['name'] === '') {
            return null;
        }
        if (!isset($template['path']) || $template['path'] === '') {
            return null;
        }

        return $template;
    }
}
