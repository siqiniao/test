<?php








namespace JsonSchema\Constraints;

use JsonSchema\Entity\JsonPointer;







class ObjectConstraint extends Constraint
{



public function check(&$element, $definition = null, JsonPointer $path = null, $additionalProp = null, $patternProperties = null)
{
if ($element instanceof UndefinedConstraint) {
return;
}

$matches = array();
if ($patternProperties) {
$matches = $this->validatePatternProperties($element, $path, $patternProperties);
}

if ($definition) {

 $this->validateDefinition($element, $definition, $path);
}


 $this->validateElement($element, $matches, $definition, $path, $additionalProp);
}

public function validatePatternProperties($element, JsonPointer $path = null, $patternProperties)
{
$try = array('/', '#', '+', '~', '%');
$matches = array();
foreach ($patternProperties as $pregex => $schema) {
$delimiter = '/';

 foreach ($try as $delimiter) {
if (strpos($pregex, $delimiter) === false) { 
 break;
}
}


 if (@preg_match($delimiter . $pregex . $delimiter . 'u', '') === false) {
$this->addError($path, 'The pattern "' . $pregex . '" is invalid', 'pregex', array('pregex' => $pregex));
continue;
}
foreach ($element as $i => $value) {
if (preg_match($delimiter . $pregex . $delimiter . 'u', $i)) {
$matches[] = $i;
$this->checkUndefined($value, $schema ?: new \stdClass(), $path, $i);
}
}
}

return $matches;
}










public function validateElement($element, $matches, $objectDefinition = null, JsonPointer $path = null, $additionalProp = null)
{
$this->validateMinMaxConstraint($element, $objectDefinition, $path);

foreach ($element as $i => $value) {
$definition = $this->getProperty($objectDefinition, $i);


 if (!in_array($i, $matches) && $additionalProp === false && $this->inlineSchemaProperty !== $i && !$definition) {
$this->addError($path, 'The property ' . $i . ' is not defined and the definition does not allow additional properties', 'additionalProp');
}


 if (!in_array($i, $matches) && $additionalProp && !$definition) {
if ($additionalProp === true) {
$this->checkUndefined($value, null, $path, $i);
} else {
$this->checkUndefined($value, $additionalProp, $path, $i);
}
}


 $require = $this->getProperty($definition, 'requires');
if ($require && !$this->getProperty($element, $require)) {
$this->addError($path, 'The presence of the property ' . $i . ' requires that ' . $require . ' also be present', 'requires');
}

$property = $this->getProperty($element, $i, $this->factory->createInstanceFor('undefined'));
if (is_object($property)) {
$this->validateMinMaxConstraint(!($property instanceof UndefinedConstraint) ? $property : $element, $definition, $path);
}
}
}








public function validateDefinition(&$element, $objectDefinition = null, JsonPointer $path = null)
{
$undefinedConstraint = $this->factory->createInstanceFor('undefined');

foreach ($objectDefinition as $i => $value) {
$property = &$this->getProperty($element, $i, $undefinedConstraint);
$definition = $this->getProperty($objectDefinition, $i);

if (is_object($definition)) {

 $this->checkUndefined($property, $definition, $path, $i);
}
}
}










protected function &getProperty(&$element, $property, $fallback = null)
{
if (is_array($element) && (isset($element[$property]) || array_key_exists($property, $element)) ) {
return $element[$property];
} elseif (is_object($element) && property_exists($element, $property)) {
return $element->$property;
}

return $fallback;
}








protected function validateMinMaxConstraint($element, $objectDefinition, JsonPointer $path = null)
{

 if (isset($objectDefinition->minProperties) && !is_object($objectDefinition->minProperties)) {
if ($this->getTypeCheck()->propertyCount($element) < $objectDefinition->minProperties) {
$this->addError($path, 'Must contain a minimum of ' . $objectDefinition->minProperties . ' properties', 'minProperties', array('minProperties' => $objectDefinition->minProperties));
}
}

 if (isset($objectDefinition->maxProperties) && !is_object($objectDefinition->maxProperties)) {
if ($this->getTypeCheck()->propertyCount($element) > $objectDefinition->maxProperties) {
$this->addError($path, 'Must contain no more than ' . $objectDefinition->maxProperties . ' properties', 'maxProperties', array('maxProperties' => $objectDefinition->maxProperties));
}
}
}
}
