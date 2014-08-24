<?php
namespace Adapter;
use \Adapter\FileData;
use \Adapter\Markdown;
use \Michelf\MarkdownExtra;

class Convert
{
	private $_htmlFile = '';
	private $_htmlCategory = '';
	private $_markdown = '';
	private $_data = array();
	public function __construct($key)
	{
		$this->_configure($key);
	}

	private function _configure($key)
	{
		$this->_data = FileData::getData();
		$data = $this->_data;
		if (!isset($data[$key])) {
			throw new \Exception("没有此文件", 1);
		} else {
			$this->_htmlCategory = HTMLPATH.$data[$key]['category']."/";
			$this->_htmlFile = $this->_htmlCategory.$key."_".$data[$key]['title'].".html";
			$this->_markdown = MARKDOWNPATH.$key."_".$data[$key]['title'].".md";
		}
	}

	public function run()
	{
		$this->readyPageContent();
		$this->readyIndexContent();
	}

	public function readyPageContent()
	{
		if ($this->_markdown) {
			if (!is_dir($this->_htmlCategory)) {
				mkdir($this->_htmlCategory, 0777);
			}
			$text = file_get_contents($this->_markdown);
			$page_html = MarkdownExtra::defaultTransform($text);

			$handle = fopen($this->_htmlFile, "w");
			$contents = fwrite($handle, $this->renderPage($page_html));
			fclose($handle);
		}
	}

	public function readyIndexContent()
	{
		if (!$this->_markdown) {
			return false;
		}

		$category = array();
		if (!empty($this->_data)) {
			foreach ($this->_data as $data) {
				$category[$data['category']][$data['key']] = $data;
			}
		}

		$html = '';
		foreach ($category as $key => $cols) {
			$listHtml = '';
			foreach ($cols as $list) {
				$listHtml = $listHtml."<li class='pagelist'>
		          <a href='./Html/{$key}/{$list['key']}_{$list['title']}.html'>{$list['title']}</a>
		        </li>";
			}
			$html = $html.<<<HTML
				<h2 id="{$key}">{$key}</h2>
		        <ul>
		        	{$listHtml}
		        </ul>
HTML;
		}

		$handle = fopen(HTMLINDEXPATH, "w");
		fwrite($handle, $this->renderIndex($html));
		fclose($handle);
	}

	public function renderPage($page_html)
	{
		$html = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8>
        <title>PHP Markdown Lib - Readme</title>
        <link href="http://demo.simiki.org/static/css/tango.css" rel="stylesheet"></link>
        <link href="http://demo.simiki.org/static/css/style.css" rel="stylesheet"></link>
    </head>
    <body>
        <div id="container">
            {$page_html}
        </div>
    </body>
</html>
HTML;

		return $html;
	}

	public function renderIndex($index_html)
	{
		$html = <<<HTML
			<!DOCTYPE HTML>
			<html>
			    <head>
			        <link rel="Stylesheet" type="text/css" href="http://demo.simiki.org/static/css/style.css">
			        <link rel="Stylesheet" type="text/css" href="http://demo.simiki.org/static/css/tango.css">
			        <title>MtaoWiKi</title>
			        <meta name="keywords" content="wiki"/>
			        <meta name="description" content="This is a demo wiki"/>
			        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			    </head>

			    <body>
			        <div id="container">
			            
				    <div id="wiki_title">MtaoWiKi</div>

				    <div id="index">
				    	{$index_html}
				      	<div class="clearfix"></div>
				    </div>

			        </div>
			        <div id="footer">
			            <span>
			                Copyright © 2012-2014 Mtao.
			            </span>
			        </div>
			        
			    </body>
			</html>
HTML;
		return $html;
	}
}
?>