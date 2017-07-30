<?php








namespace JsonSchema\Constraints;

use JsonSchema\Constraints\TypeCheck\LooseTypeCheck;
use JsonSchema\Entity\JsonPointer;
use JsonSchema\Uri\UriResolver;







class UndefinedConstraint extends Constraint
{



public function check(&$value, $schema = null, JsonPointer $path = null, $i = null)
{
if (is_null($schema) || !is_object($schema)) {
return;
}

$path = $this->incrementPath($path ?: new JsonPointer(''), $i);


 $this->validateCommonProperties($value, $schema, $path, $i);


 $this->validateOfProperties($value, $schema, $path, '');


 $this->validateTypes($value, $schema, $path, $i);
}









public function validateTypes(&$value, $schema = null, JsonPointer $path, $i = null)
{

 if ($this->getTypeCheck()->isArray($value)) {
$this->checkArray($value, $schema, $path, $i);
}


 if (LooseTypeCheck::isObject($value)) { 
 
 
 $this->checkObject(
$value,
isset($schema->properties) ? $this->factory->getSchemaStorage()->resolveRefSchema($schema->properties) : $schema,
$path,
isset($schema->additionalProperties) ? $schema->additionalProperties : null,
isset($schema->patternProperties) ? $schema->patternProperties : null
);
}


 if (is_string($value)) {
$this->checkString($value, $schema, $path, $i);
}


 if (is_numeric($value)) {
$this->checkNumber($value, $schema, $path, $i);
}


 if (isset($schema->enum)) {
$this->checkEnum($value, $schema, $path, $i);
}
}









protected function validateCommonProperties(&$value, $schema = null, JsonPointer $path, $i = '')
{

 if (isset($schema->extends)) {
if (is_string($schema->extends)) {
$schema->extends = $this->validateUri($schema, $schema->extends);
}
if (is_array($schema->extends)) {
foreach ($schema->extends as $extends) {
$this->checkUndefined($value, $extends, $path, $i);
}
} else {
$this->checkUndefined($value, $schema->extends, $path, $i);
}
}


 if ($this->factory->getConfig(self::CHECK_MODE_APPLY_DEFAULTS)) {
if ($this->getTypeCheck()->isObject($value) && isset($schema->properties)) {

 foreach ($schema->properties as $i => $propertyDefinition) {
if (!$this->getTypeCheck()->propertyExists($value, $i) && isset($propertyDefinition->default)) {
if (is_object($propertyDefinition->default)) {
$this->getTypeCheck()->propertySet($value, $i, clone $propertyDefinition->default);
} else {
$this->getTypeCheck()->propertySet($value, $i, $propertyDefinition->default);
}
}
}
} elseif ($this->getTypeCheck()->isArray($value)) {
if (isset($schema->properties)) {

 foreach ($schema->properties as $i => $propertyDefinition) {
if (!isset($value[$i]) && isset($propertyDefinition->default)) {
if (is_object($propertyDefinition->default)) {
$value[$i] = clone $propertyDefinition->default;
} else {
$value[$i] = $propertyDefinition->default;
}
}
}
} elseif (isset($schema->items)) {

 foreach ($schema->items as $i => $itemDefinition) {
if (!isset($value[$i]) && isset($itemDefinition->default)) {
if (is_object($itemDefinition->default)) {
$value[$i] = clone $itemDefinition->default;
} else {
$value[$i] = $itemDefinition->default;
}
}
}
}
} elseif (($value instanceof self || $value === null) && isset($schema->default)) {

 $value = is_object($schema->default) ? clone $schema->default : $schema->default;
}
}


 if ($this->getTypeCheck()->isObject($value)) {
if (!($value instanceof self) && isset($schema->required) && is_array($schema->required)) {

 foreach ($schema->required as $required) {
if (!$this->getTypeCheck()->propertyExists($value, $required)) {
$this->addError(
$this->incrementPath($path ?: new JsonPointer(''), $required),
'The property ' . $required . ' is required',
'required'
);
}
}
} elseif (isset($schema->required) && !is_array($schema->required)) {

 if ($schema->required && $value instanceof self) {
$this->addError($path, 'Is missing and it is required', 'required');
}
}
}


 if (!($value instanceof self)) {
$this->checkType($value, $schema, $path, $i);
}


 if (isset($schema->disallow)) {
$initErrors = $this->getErrors();

$typeSchema = new \stdClass();
$typeSchema->type = $schema->disallow;
$this->checkType($value, $typeSchema, $path);


 if (count($this->getErrors()) == count($initErrors)) {
$this->addError($path, 'Disallowed value was matched', 'disallow');
} else {
$this->errors = $initErrors;
}
}

if (isset($schema->not)) {
$initErrors = $this->getErrors();
$this->checkUndefined($value, $schema->not, $path, $i);


 if (count($this->getErrors()) == count($initErrors)) {
$this->addError($path, 'Matched a schema which it should not', 'not');
} else {
$this->errors = $initErrors;
}
}


 if (isset($schema->dependencies) && $this->getTypeCheck()->isObject($value)) {
$this->validateDependencies($value, $schema->dependencies, $path);
}
}









protected function validateOfProperties(&$value, $schema, JsonPointer $path, $i = '')
{

 if ($value instanceof self) {
return;
}

if (isset($schema->allOf)) {
$isValid = true;
foreach ($schema->allOf as $allOf) {
$initErrors = $this->getErrors();
$this->checkUndefined($value, $allOf, $path, $i);
$isValid = $isValid && (count($this->getErrors()) == count($initErrors));
}
if (!$isValid) {
$this->addError($path, 'Failed to match all schemas', 'allOf');
}
}

if (isset($schema->anyOf)) {
$isValid = false;
$startErrors = $this->getErrors();
foreach ($schema->anyOf as $anyOf) {
$initErrors = $this->getErrors();
$this->checkUndefined($value, $anyOf, $path, $i);
if ($isValid = (count($this->getErrors()) == count($initErrors))) {
break;
}
}
if (!$isValid) {
$this->addError($path, 'Failed to match at least one schema', 'anyOf');
} else {
$this->errors = $startErrors;
}
}

if (isset($schema->oneOf)) {
$allErrors = array();
$matchedSchemas = 0;
$startErrors = $this->getErrors();
foreach ($schema->oneOf as $oneOf) {
$this->errors = array();
$this->checkUndefined($value, $oneOf, $path, $i);
if (count($this->getErrors()) == 0) {
$matchedSchemas++;
}
$allErrors = array_merge($allErrors, array_values($this->getErrors()));
}
if ($matchedSchemas !== 1) {
$this->addErrors(array_merge($allErrors, $startErrors));
$this->addError($path, 'Failed to match exactly one schema', 'oneOf');
} else {
$this->errors = $startErrors;
}
}
}









protected function validateDependencies($value, $dependencies, JsonPointer $path, $i = '')
{
foreach ($dependencies as $key => $dependency) {
if ($this->getTypeCheck()->propertyExists($value, $key)) {
if (is_string($dependency)) {

 if (!$this->getTypeCheck()->propertyExists($value, $dependency)) {
$this->addError($path, "$key depends on $dependency and $dependency is missing", 'dependencies');
}
} elseif (is_array($dependency)) {

 foreach ($dependency as $d) {
if (!$this->getTypeCheck()->propertyExists($value, $d)) {
$this->addError($path, "$key depends on $d and $d is missing", 'dependencies');
}
}
} elseif (is_object($dependency)) {

 $this->checkUndefined($value, $dependency, $path, $i);
}
}
}
}

protected function validateUri($schema, $schemaUri = null)
{
$resolver = new UriResolver();
$retriever = $this->factory->getUriRetriever();

$jsonSchema = null;
if ($resolver->isValid($schemaUri)) {
$schemaId = property_exists($schema, 'id') ? $schema->id : null;
$jsonSchema = $retriever->retrieve($schemaId, $schemaUri);
}

return $jsonSchema;
}
}
