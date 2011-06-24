<?php

class DeprecatedWriter extends HTMLWriter {

	function deprecatedWriter(&$doclet) {
	
		parent::HTMLWriter($doclet);
		
		$rootDoc =& $this->_doclet->rootDoc();
		
		$doc = new DomDocument();
		$doc->preserveWhiteSpace = FALSE;
		$doc->formatOutput = TRUE;
		
		$dom_deprecated = $doc->createElement('deprecated');
		
        $deprecatedClasses = array();
        $deprecatedFields = array();
        $deprecatedMethods = array();
		$deprecatedConstants = array();
		$deprecatedGlobals = array();
		$deprecatedFunctions = array();
		
		$classes =& $rootDoc->classes();
        if ($classes) {
            foreach ($classes as $class) {
                if ($class->tags('@deprecated')) $deprecatedClasses[] = $class;
                $fields =& $class->fields();
                if ($fields) {
                    foreach ($fields as $field) {
                        if ($field->tags('@deprecated')) $deprecatedFields[] = $field;
                    }
                }
                $classes =& $class->methods();
                if ($classes) {
                    foreach ($classes as $method) {
                        if ($method->tags('@deprecated')) $deprecatedMethods[] = $method;
                    }
                }
				$constants =& $class->constants();
                if ($constants) {
                    foreach ($constants as $constant) {
                        if ($constant->tags('@deprecated')) $deprecatedConstants[] = $constant;
                    }
                }
            }
        }
        
        $globals =& $rootDoc->globals();
        if ($globals) {
            foreach ($globals as $global) {
                if ($global->tags('@deprecated')) $deprecatedGlobals[] = $global;
            }
        }
        
        $functions =& $rootDoc->functions();
        if ($functions) {
            foreach ($functions as $function) {
                if ($function->tags('@deprecated')) $deprecatedFunctions[] = $function;
            }
        }
        
        if ($deprecatedClasses) {
	
			$dom_list = $doc->createElement('classes');
			$dom_list->setAttribute('type', 'Classes');
	
            foreach($deprecatedClasses as $item) {
	
				$textTag =& $item->tags('@text');
				
				$dom_item = $doc->createElement('class');
				
				$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_item);
								
				$dom_location = $doc->createElement('location', $item->sourceFilename());
				$dom_location->setAttribute('line', $item->sourceLine());
				$dom_item->appendChild($dom_location);
				
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);

        }

		if ($deprecatedFields) {
	
			$dom_list = $doc->createElement('fields');
			$dom_list->setAttribute('type', 'Fields');
	
            foreach($deprecatedFields as $item) {
	
				$textTag =& $item->tags('@text');
				
				$dom_item = $doc->createElement('field');
				
				$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_item);
				
				$dom_location = $doc->createElement('location', $item->sourceFilename());
				$dom_location->setAttribute('line', $item->sourceLine());
				$dom_item->appendChild($dom_location);
				
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('class', $item->containingClass()->name());
				$dom_item->setAttribute('package', $item->packageName());
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
        }
        
        if ($deprecatedConstants) {
	
			$dom_list = $doc->createElement('constants');
			$dom_list->setAttribute('type', 'Constants');
	
            foreach($deprecatedConstants as $item) {
	
				$textTag =& $item->tags('@text');
				
				$dom_item = $doc->createElement('constant');
				
				$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_item);
				
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('class', $item->containingClass()->name());
				$dom_item->setAttribute('package', $item->packageName());
				
				$dom_location = $doc->createElement('location', $item->sourceFilename());
				$dom_location->setAttribute('line', $item->sourceLine());
				$dom_item->appendChild($dom_location);
				
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
        }
        
        if ($deprecatedMethods) {
		
			$dom_list = $doc->createElement('methods');
			$dom_list->setAttribute('type', 'Methods');
	
            foreach($deprecatedMethods as $item) {
	
				$textTag =& $item->tags('@text');
				
				$dom_item = $doc->createElement('method');
				
				$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_item);
				
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('class', $item->containingClass()->name());
				$dom_item->setAttribute('package', $item->packageName());
				
				$dom_location = $doc->createElement('location', $item->sourceFilename());
				$dom_location->setAttribute('line', $item->sourceLine());
				$dom_item->appendChild($dom_location);
				
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
	
        }
        
        if ($deprecatedGlobals) {
	
			$dom_list = $doc->createElement('constants');
			$dom_list->setAttribute('type', 'Constants');
	
            foreach($deprecatedGlobals as $item) {
	
				$textTag =& $item->tags('@text');
				
				$dom_item = $doc->createElement('global');
				
				$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_item);
				
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('package', $item->packageName());
				
				$dom_location = $doc->createElement('location', $item->sourceFilename());
				$dom_location->setAttribute('line', $item->sourceLine());
				$dom_item->appendChild($dom_location);
				
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
		}
        
        if ($deprecatedFunctions) {
            
			$dom_list = $doc->createElement('functions');
			$dom_list->setAttribute('type', 'Functions');
	
            foreach($deprecatedFunctions as $item) {
	
				$textTag =& $item->tags('@text');
				
				$dom_item = $doc->createElement('function');
				
				$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_item);
				
				$dom_item->setAttribute('name', $item->name());
				$dom_item->setAttribute('package', $item->packageName());
				
				$dom_location = $doc->createElement('location', $item->sourceFilename());
				$dom_location->setAttribute('line', $item->sourceLine());
				$dom_item->appendChild($dom_location);
				
				$dom_list->appendChild($dom_item);

            }
            
			$dom_deprecated->appendChild($dom_list);
			
        }

		$doc->appendChild($dom_deprecated);
        $this->_output = $doc->saveXML();
        $this->_write('deprecated.xml');
	
	}
  
}