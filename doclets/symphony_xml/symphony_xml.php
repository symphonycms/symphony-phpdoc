<?php

require('htmlWriter.php');
require('packageWriter.php');
require('classWriter.php');
require('functionWriter.php');
require('globalWriter.php');
require('deprecatedWriter.php');
require('delegateWriter.php');

class Symphony_Xml extends Doclet {

	function formatLink($url, $text)
    {
        return array('text'=>$text, 'url'=>$url);
    }

	var $_rootDoc;
	var $_d;
	var $_windowTitle = '';
	var $_docTitle = '';
	var $_header = '';
	var $_footer = '';
	var $_bottom = '';
	var $_includeSource = FALSE;

	function symphony_xml(&$rootDoc) {
	
		$this->_rootDoc =& $rootDoc;
		$phpdoctor =& $rootDoc->phpdoctor();
		$options =& $rootDoc->options();
		
		if (isset($options['d'])) {
			$this->_d = $phpdoctor->makeAbsolutePath($options['d'], $phpdoctor->sourcePath());
		} elseif (isset($options['output_dir'])) {
			$this->_d = $phpdoctor->makeAbsolutePath($options['output_dir'], $phpdoctor->sourcePath());
		} else {
			$this->_d = $phpdoctor->makeAbsolutePath('apidocs', $phpdoctor->sourcePath());
		}
		$this->_d = $phpdoctor->fixPath($this->_d);
		
		if (is_dir($this->_d)) {
			$phpdoctor->warning('Output directory already exists, overwriting');
		} else {
			mkdir($this->_d);
		}
		$phpdoctor->verbose('Setting output directory to "'.$this->_d.'"');
		
		$packageWriter =& new packageWriter($this);
		$classWriter =& new classWriter($this);
		$functionWriter =& new functionWriter($this);
		$globalWriter =& new globalWriter($this);
		$deprecatedWriter =& new deprecatedWriter($this);
		$delegateWriter =& new delegateWriter($this);
	
	}

	function &rootDoc() {
		return $this->_rootDoc;
	}
	
	function &phpdoctor() {
		return $this->_rootDoc->phpdoctor();
	}

	function destinationPath() {
		return $this->_d;
	}

	function windowTitle() {
		return $this->_windowTitle;
	}

	function docTitle() {
		return $this->_docTitle;
	}

	function getHeader() {
		return $this->_header;
	}

	function getFooter() {
		return $this->_footer;
	}

	function bottom() {
		return $this->_bottom;
	}
	
	function version() {
		$phpdoctor =& $this->_rootDoc->phpdoctor();
		return $phpdoctor->version();
	}
	
	function includeSource() {
	    return $this->_includeSource;
	}
}