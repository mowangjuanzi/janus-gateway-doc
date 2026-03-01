<?php

use Dom\HTMLDocument;
use Dom\HTMLElement;

require __DIR__ . '/vendor/autoload.php';

class Spider
{
    protected string $urlPrefix = 'https://janus.conf.meetecho.com/docs/';

    /**
     * @var array 待抓取的URL
     */
    protected array $urlOrigins = [
//        'index.html',
        'pages.html',
    ];

    /**
     * @var array 已经结束抓取的 URL
     */
    protected array $urlFinish = [];

    protected string $savePath = '';
    public function __construct()
    {
        $this->savePath = realpath('../janus-html');
    }

    public function start()
    {
        $urlOriginal = array_shift($this->urlOrigins) ?? '';

        if ($urlOriginal) {
            $html = file_get_contents($this->urlPrefix . $urlOriginal);

            $dom = HTMLDocument::createFromString($html, LIBXML_NOERROR);

            $content = $dom->querySelector('div.contents');

            $this->urlFinish[] = $urlOriginal;

            if ($urlOriginal !== 'pages.html') {
                $content = $content->querySelector('div.textblock');
                file_put_contents($this->savePath . '/' . $urlOriginal, $content->innerHTML);
            } else {
                // 获取所有的 URL
                $as = $content->querySelectorAll('a');

                /** @var HTMLElement $a */
                foreach ($as as $a) {
                    foreach ($a->attributes as $item) {
                        if ($item->nodeName === 'href') {
                            $url = $item->nodeValue;

                            if (!in_array($url, $this->urlFinish)) {
                                $this->urlOrigins[] = $url;
                            }
                        }
                    }
                }
            }
            $this->start();
        }
    }
}

$spider = new Spider();
$spider->start();
