<?php








namespace JsonSchema;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\InvalidConfigException;









class Validator extends BaseConstraint
{
const SCHEMA_MEDIA_TYPE = 'application/schema+json';










public function validate(&$value, $schema = null, $checkMode = null)
{
$initialCheckMode = $this->factory->getConfig();
if ($checkMode !== null) {
$this->factory->setConfig($checkMode);
}

$validator = $this->factory->createInstanceFor('schema');
$validator->check($value, $schema);

$this->factory->setConfig($initialCheckMode);

$this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
}




public function check($value, $schema)
{
return $this->validate($value, $schema);
}




public function coerce(&$value, $schema)
{
return $this->validate($value, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
}
}
