<?php

namespace Sb\Browser\Dom;

use Sb\Browser\Form;
use simplehtmldom\HtmlDocument;

class Dom
{
    private $content;

    private $dom;

    private $url;

    public function __construct($content, $encoding = 'UTF-8', $url = '')
    {
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $this->content = $content;
        $this->dom = new HtmlDocument($content);
        $this->url = $url;
    }

    public function find($selector, $dom = null)
    {
        if (null === $dom) {
            $dom = $this->dom;
        }

        return $dom->find($selector);
    }

    public function findFirst($selector, $dom = null)
    {
        if (null === $dom) {
            $dom = $this->dom;
        }

        return $dom->find($selector, 0);
    }

    public function findNElement($selector, $idx, $dom = null)
    {
        if (null === $dom) {
            $dom = $this->dom;
        }

        return $dom->find($selector, $idx);
    }

    public function findForm($selector, $dom = null)
    {
        $form = new Form($this->findFirst($selector, $dom));
        $form->setBaseUrl($this->url);
        return $form;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

}