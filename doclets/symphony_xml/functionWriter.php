<?php

class FunctionWriter extends HTMLWriter {

	function functionWriter(&$doclet) {
	
		parent::HTMLWriter($doclet);

		$rootDoc =& $this->_doclet->rootDoc();
        
        $packages =& $rootDoc->packages();
        ksort($packages);

		foreach($packages as $packageName => $package) {
			
			$doc = new DomDocument();
			$doc->preserveWhiteSpace = FALSE;
			$doc->formatOutput = TRUE;
			
			$dom_functions = $doc->createElement('functions');
			$dom_functions->setAttribute('package', $package->name());
			
			$functions =& $package->functions();
				
			if ($functions) {
                ksort($functions);

				foreach($functions as $function) {
					
					$dom_function = $doc->createElement('function');
					
					$dom_modifiers = $doc->createElement('modifiers');
					foreach(explode(' ', trim($function->modifiers())) as $modifier) {
						$dom_modifiers->appendChild($doc->createElement('modifier', $modifier));
					}
					$dom_function->appendChild($dom_modifiers);
					
					$type = $function->returnTypeAsString();
					$type = $this->__removeTextFromMarkup($type);
					
					$dom_function->setAttribute('name', $function->name());
					$dom_function->setAttribute('return', $type);
					
					$dom_signature = $doc->createElement('parameters');
					$this->getSignature($function, $doc, $dom_signature);
					$dom_function->appendChild($dom_signature);
					
					$dom_location = $doc->createElement('location', $function->sourceFilename());
					$dom_location->setAttribute('line', $function->sourceLine());
					$dom_function->appendChild($dom_location);
					
					$textTag =& $function->tags('@text');
					$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_function);
					
					$dom_functions->appendChild($dom_function);
					
				}

			}
			
			$doc->appendChild($dom_functions);
			$this->_output = $doc->saveXML();
			$this->_write($package->asPath().'/package-functions.xml');
		}
	
	}

}