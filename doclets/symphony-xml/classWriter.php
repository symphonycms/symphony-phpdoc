<?php

class ClassWriter extends HTMLWriter {

	function classWriter(&$doclet) {
	
		parent::HTMLWriter($doclet);

		$rootDoc =& $this->_doclet->rootDoc();
		$phpdoctor =& $this->_doclet->phpdoctor();
		$packages =& $rootDoc->packages();
		
        ksort($packages);

		foreach ($packages as $packageName => $package) {
			
			$classes =& $package->allClasses();
			
			if ($classes) {
                ksort($classes);
				foreach ($classes as $name => $class) {
					
					$doc = new DomDocument();
					$doc->preserveWhiteSpace = FALSE;
					$doc->formatOutput = TRUE;
					
					$dom_class = $doc->createElement('class');
					
					if ($class->isInterface()) {
						$dom_class->setAttribute('type', 'interface');
					} else {
						$dom_class->setAttribute('type', 'class');
					}
					
					$dom_class->setAttribute('name', $class->name());
					$dom_class->setAttribute('handle', strtolower($class->name()));
					
					$dom_package = $doc->createElement('package');
					$dom_package->setAttribute('name', $class->packageName());
					$dom_package->setAttribute('handle', $class->packageName());
					$dom_class->appendChild($dom_package);
					
					$dom_location = $doc->createElement('location', $class->sourceFilename());
					$dom_location->setAttribute('line', $class->sourceLine());
					$dom_class->appendChild($dom_location);
					
					$implements =& $class->interfaces();
					if (count($implements) > 0) {						
						$dom_interfaces = $doc->createElement('interfaces');
						foreach ($implements as $interface) {							
							$dom_interface = $doc->createElement('interface', $interface->name());
							$dom_interface->setAttribute('package', $interface->packageName());
							
							$dom_interfaces->appendChild($dom_interface);
						}
						$dom_class->appendChild($dom_interfaces);
					}
					
					$dom_modifiers = $doc->createElement('modifiers');
					foreach(explode(' ', trim($class->modifiers())) as $modifier) {
						$dom_modifiers->appendChild($doc->createElement('modifier', $modifier));
					}
					$dom_class->appendChild($dom_modifiers);
					
					if ($class->superclass()) {
						$superclass =& $rootDoc->classNamed($class->superclass());
						if ($superclass) {
							$dom_superclass = $doc->createElement('superclass', $superclass->name());
							$dom_superclass->setAttribute('package', $superclass->packageName());
						} else {
							$dom_superclass = $doc->createElement('superclass', $class->superclass());
						}
						$dom_class->appendChild($dom_superclass);
					}
					
					$textTag =& $class->tags('@text');
					$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_class);
					
					$this->_processTags($class->tags(), $doc, $dom_class);

					$constants =& $class->constants();
                    ksort($constants);
					$fields =& $class->fields();
                    ksort($fields);
					$methods =& $class->methods();
                    ksort($methods);

					if ($constants) {
						
						$dom_constants = $doc->createElement('constants');

						foreach ($constants as $field) {
							
							$textTag =& $field->tags('@text');
							
							$dom_constant = $doc->createElement('constant');
							
							$dom_modifiers = $doc->createElement('modifiers');
							foreach(explode(' ', trim($field->modifiers())) as $modifier) {
								$dom_modifiers->appendChild($doc->createElement('modifier', $modifier));
							}
							$dom_constant->appendChild($dom_modifiers);
							
							$type = $field->typeAsString();
							$type = $this->__removeTextFromMarkup($type);
							
							$dom_constant->setAttribute('name', ((!$field->constantValue()) ? "$" : "") . $field->name());
							$dom_constant->setAttribute('type', $type);
							
							if ($field->value()) $dom_constant->setAttribute('value', htmlspecialchars($field->value()));
							
							$dom_constant_location = $doc->createElement('location', $field->sourceFilename());
							$dom_constant_location->setAttribute('line', $field->sourceLine());
							$dom_constant->appendChild($dom_constant_location);
							
							$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_constant);
							
							$this->_processTags($field->tags(), $doc, $dom_constant);
							
							$dom_constants->appendChild($dom_constant);
							
						}
						
						$dom_class->appendChild($dom_constants);
					}
					
					if ($fields) {
						
						$dom_fields = $doc->createElement('fields');
						
						foreach ($fields as $field) {
							
							$textTag =& $field->tags('@text');
							
							$dom_field = $doc->createElement('field');
							
							$dom_modifiers = $doc->createElement('modifiers');
							foreach(explode(' ', trim($field->modifiers())) as $modifier) {
								$dom_modifiers->appendChild($doc->createElement('modifier', $modifier));
							}
							$dom_field->appendChild($dom_modifiers);
							
							$type = $field->typeAsString();
							$type = $this->__removeTextFromMarkup($type);
							
							$dom_field->setAttribute('name', ((!$field->constantValue()) ? "$" : "") . $field->name());
							$dom_field->setAttribute('type', $type);
							
							if ($field->value()) $dom_field->setAttribute('value', htmlspecialchars($field->value()));
							
							$dom_field_location = $doc->createElement('location', $field->sourceFilename());
							$dom_field_location->setAttribute('line', $field->sourceLine());
							$dom_field->appendChild($dom_field_location);
							
							$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_field);
							
							$this->_processTags($field->tags(), $doc, $dom_field);
							
							$dom_fields->appendChild($dom_field);
						}
						
						$dom_class->appendChild($dom_fields);
						
					}
					
					if ($class->superclass()) {
                        $superclass =& $rootDoc->classNamed($class->superclass());
                        if ($superclass) {
							$dom_inherited_fields = $doc->createElement('inherited-fields');
                            $this->inheritFields($superclass, $rootDoc, $package, $doc, $dom_inherited_fields);
							$dom_class->appendChild($dom_inherited_fields);
                        }
					}

					if ($methods) {
						
						$dom_methods = $doc->createElement('methods');
						
                        foreach($methods as $method) {
                            
							$textTag =& $method->tags('@text');
							
							$dom_method = $doc->createElement('method');
							
							$dom_modifiers = $doc->createElement('modifiers');
							foreach(explode(' ', trim($method->modifiers())) as $modifier) {
								$dom_modifiers->appendChild($doc->createElement('modifier', $modifier));
							}
							$dom_method->appendChild($dom_modifiers);
							
							$type = $method->returnTypeAsString();
							$type = $this->__removeTextFromMarkup($type);
							
							$dom_method->setAttribute('name', $method->name());
							$dom_method->setAttribute('return', $type);
							
							$dom_signature = $doc->createElement('parameters');
							$this->getSignature($method, $doc, $dom_signature);
							$dom_method->appendChild($dom_signature);
							
							$dom_method_location = $doc->createElement('location', $method->sourceFilename());
							$dom_method_location->setAttribute('line', $method->sourceLine());
							$dom_method->appendChild($dom_method_location);
							
							$this->appendDescription($this->_processInlineTags($textTag), $doc, $dom_method);
							
							$this->_processTags($method->tags(), $doc, $dom_method);
							
							$dom_methods->appendChild($dom_method);
                        }

						$dom_class->appendChild($dom_methods);

					}
					
					if ($class->superclass()) {
                        $superclass =& $rootDoc->classNamed($class->superclass());
                        if ($superclass) {
							$dom_inherited_methods = $doc->createElement('inherited-methods');
                            $this->inheritMethods($superclass, $rootDoc, $package, $doc, $dom_inherited_methods);
							$dom_class->appendChild($dom_inherited_methods);
                        }
					}
					
					$doc->appendChild($dom_class);
					
					$this->_output = $doc->saveXML();
					$this->_write($package->asPath().'/'.strtolower($class->name()).'.xml');
					
				}
			}
		}
    }

	
	/** Display the inherited fields of an element. This method calls itself
	 * recursively if the element has a parent class.
	 *
	 * @param ProgramElementDoc element
	 * @param RootDoc rootDoc
	 * @param PackageDoc package
	 */
	function inheritFields(&$element, &$rootDoc, &$package, $doc, &$dom_wrapper)
    {
		$fields =& $element->fields();
		
		if ($fields) {
			
            ksort($fields);
			
			$dom_class = $doc->createElement('class');
			
			$dom_class->setAttribute('name', $element->_name);
			$dom_class->setAttribute('package', $element->_package);
			
			foreach($fields as $field) {
				
				//$dom_wrapper->setAttribute('package', $field->packageName());				
				//$class =& $field->containingClass();
				//$dom_wrapper->setAttribute('class', $class->name());				
				
				$dom_field = $doc->createElement('field');
				$dom_field->setAttribute('name', $field->name());
				
				$dom_class->appendChild($dom_field);
				
			}
			
			$dom_wrapper->appendChild($dom_class);
			
			if ($element->superclass()) {
                $superclass =& $rootDoc->classNamed($element->superclass());
                if ($superclass) {
                    $this->inheritFields($superclass, $rootDoc, $package, $doc, $dom_wrapper);
                }
			}
		}

	}
	
	/** Display the inherited methods of an element. This method calls itself
	 * recursively if the element has a parent class.
	 *
	 * @param ProgramElementDoc element
	 * @param RootDoc rootDoc
	 * @param PackageDoc package
	 */
	function inheritMethods(&$element, &$rootDoc, &$package, $doc, &$dom_wrapper)
    {
		$methods =& $element->methods();
		if ($methods) {
            ksort($methods);
			
			$dom_class = $doc->createElement('class');
			
			$dom_class->setAttribute('name', $element->_name);
			$dom_class->setAttribute('package', $element->_package);
			
			foreach($methods as $method) {
				
				$dom_wrapper->setAttribute('package', $method->packageName());				
				$class =& $method->containingClass();
				$dom_wrapper->setAttribute('class', $class->name());
				
				$dom_method = $doc->createElement('method');
				$dom_method->setAttribute('name', $method->name());
				
				$dom_class->appendChild($dom_method);
			}
			
			$dom_wrapper->appendChild($dom_class);

			if ($element->superclass()) {
                $superclass =& $rootDoc->classNamed($element->superclass());
                if ($superclass) {
                    $this->inheritMethods($superclass, $rootDoc, $package, $doc, $dom_wrapper);
                }
			}
		}
	}

}