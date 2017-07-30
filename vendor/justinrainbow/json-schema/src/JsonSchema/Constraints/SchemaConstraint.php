<?php








namespace JsonSchema\Constraints;

use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\InvalidArgumentException;







class SchemaConstraint extends Constraint
{



public function check(&$element, $schema = null, JsonPointer $path = null, $i = null)
{
if ($schema !== null) {

 $this->checkUndefined($element, $schema, $path, $i);
} elseif ($this->getTypeCheck()->propertyExists($element, $this->inlineSchemaProperty)) {
$inlineSchema = $this->getTypeCheck()->propertyGet($element, $this->inlineSchemaProperty);
if (is_array($inlineSchema)) {
$inlineSchema = json_decode(json_encode($inlineSchema));
}

 $this->checkUndefined($element, $inlineSchema, $path, $i);
} else {
throw new InvalidArgumentException('no schema found to verify against');
}
}
}
