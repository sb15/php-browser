<?php

namespace Sb\Browser\Dom;

use Sb\Browser\Form;
use Sunra\PhpSimple\HtmlDomParser;

class Dom
{
    private $content;

    /** @var \simple_html_dom */
    private $dom;

    private $url;

    public function __construct($content, $encoding = 'UTF-8', $url = '')
    {
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $this->content = $content;
        $this->dom = HtmlDomParser::str_get_html($content);
        $this->url = $url;
    }

    /**
     * @param $selector
     * @param \simple_html_dom_node|null $dom
     * @return \simple_html_dom_node[]
     */
    public function find($selector, $dom = null)
    {
        if (null === $dom) {
            $dom = $this->dom;
        }

        return $dom->find($selector);
    }

    /**
     * @param $selector
     * @param \simple_html_dom_node/null $dom
     * @return \simple_html_dom_node
     */
    public function findFirst($selector, $dom = null)
    {
        if (null === $dom) {
            $dom = $this->dom;
        }

        return $dom->find($selector, 0);
    }

    /**
     * @param $selector
     * @param \simple_html_dom_node $dom
     * @param int $idx
     * @return \simple_html_dom_node
     */
    public function findNElement($selector, $idx, $dom = null)
    {
        if (null === $dom) {
            $dom = $this->dom;
        }

        return $dom->find($selector, $idx);
    }

    /**
     * @param $selector
     * @param \simple_html_dom_node $dom
     * @return Form
     */
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