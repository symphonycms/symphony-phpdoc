<?php

class PackageWriter extends HTMLWriter {

	function packageWriter(&$doclet) {
	
		parent::HTMLWriter($doclet);
        
		$rootDoc =& $this->_doclet->rootDoc();
		$phpdoctor =& $this->_doclet->phpdoctor();

        $packages =& $rootDoc->packages();
        ksort($packages);

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = FALSE;
		$doc->formatOutput = TRUE;
		
		$dom_packages = $doc->createElement('packages');
        
		foreach($packages as $packageName => $package) {
			
			$dom_package = $doc->createElement('package');
			$dom_package->setAttribute('name', $package->name());
			$dom_package->setAttribute('handle', strtolower($package->name()));
			
			/*
			$textTag =& $package->tags('@text');
			if ($textTag) {				
				$dom_package->appendChild($doc->createElement('description', $this->_processInlineTags($textTag, TRUE)));
			}
			*/

			$classes =& $package->ordinaryClasses();
			if ($classes) {
				
				ksort($classes);				
				$dom_items = $doc->createElement('classes');
				
				/*
				A. Recursive nested tree of classes
				*/
				$tree = array();
				foreach ($classes as $class) {
					$this->_buildTree($tree, $class);
				}
				
				$this->_displayTree($tree, NULL, $doc, $dom_items);
				
				/*
				B. Non-nested list of classes
				
				foreach($classes as $name => $class) {
					
					$description = NULL;
					$textTag =& $classes[$name]->tags('@text');
					if ($textTag) $description = $this->_processInlineTags($textTag);
					
					$dom_item = $doc->createElement('class', $description);
					$dom_item->setAttribute('name', $classes[$name]->name());
					$dom_item->setAttribute('path', str_repeat('../', $this->_depth), $classes[$name]->asPath());
					
					$dom_items->appendChild($dom_item);
				}				
				*/
				
				$dom_package->appendChild($dom_items);
				
			}

			$interfaces =& $package->interfaces();
			if ($interfaces) {
                ksort($interfaces);
				$this->buildElements('interfaces', 'interface', $interfaces, $doc, $dom_package);
			}

			$exceptions =& $package->exceptions();
			if ($exceptions) {
                ksort($exceptions);
				$this->buildElements('exceptions', 'exception', $exceptions, $doc, $dom_package);
			}
			
			$functions =& $package->functions();
			if ($functions) {
                ksort($functions);
				$this->buildElements('functions', 'function', $functions, $doc, $dom_package);
			}
			
			$globals =& $package->globals();
			if ($globals) {
                ksort($globals);
				$this->buildElements('constants', 'constant', $globals, $doc, $dom_package);
			}
						
			$dom_packages->appendChild($dom_package);
			
		}
		
		$doc->appendChild($dom_packages);
		
		$this->_output = $doc->saveXML();
		$this->_write('packages.xml');

	}
	
	function buildElements($group_type, $type, $collection, $doc, &$dom_wrapper) {
		
		$dom_items = $doc->createElement($group_type);
		
		foreach($collection as $name => $item) {
			
			$dom_item = $doc->createElement($type);
			
			$textTag =& $collection[$name]->tags('@text');
			$this->appendDescription($this->_processInlineTags($textTag, TRUE), $doc, $dom_item);
			$dom_item->setAttribute('name', $collection[$name]->name());
			
			$dom_items->appendChild($dom_item);
		}
		
		$dom_wrapper->appendChild($dom_items);
		
	}
	
	/**
	 * Build the class tree branch for the given element
	 */
	function _buildTree(&$tree, &$element)
    {
		$tree[$element->name()] = $element;
		if ($element->superclass()) {
			$rootDoc =& $this->_doclet->rootDoc();
            $superclass =& $rootDoc->classNamed($element->superclass());
            if ($superclass) {
                $this->_buildTree($tree, $superclass);
            }
		}
	}
	
	/**
	 * Build the class tree branch for the given element
	 */
	function _displayTree($tree, $parent=NULL, $doc, &$dom_wrapper)
    {
		$outputList = TRUE;
		foreach($tree as $name => $element) {
			if ($element->superclass() == $parent) {
				
				$dom_item = $doc->createElement('class');
				
				$description = NULL;
				$textTag =& $element->tags('@text');
				$this->appendDescription($this->_processInlineTags($textTag, TRUE), $doc, $dom_item);
				
				$dom_item->setAttribute('name', $element->name());
				$dom_item->setAttribute('handle', strtolower($element->name()));
				
				$this->_displayTree($tree, $name, $doc, $dom_item);
				
				$dom_wrapper->appendChild($dom_item);
				
			}
		}
		
	}

}