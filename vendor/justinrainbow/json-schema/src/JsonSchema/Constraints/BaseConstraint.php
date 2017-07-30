<?php








namespace JsonSchema\Constraints;

use JsonSchema\Entity\JsonPointer;
use JsonSchema\Exception\ValidationException;





class BaseConstraint
{



protected $errors = array();




protected $factory;




public function __construct(Factory $factory = null)
{
$this->factory = $factory ?: new Factory();
}

public function addError(JsonPointer $path = null, $message, $constraint = '', array $more = null)
{
$error = array(
'property' => $this->convertJsonPointerIntoPropertyPath($path ?: new JsonPointer('')),
'pointer' => ltrim(strval($path ?: new JsonPointer('')), '#'),
'message' => $message,
'constraint' => $constraint,
);

if ($this->factory->getConfig(Constraint::CHECK_MODE_EXCEPTIONS)) {
throw new ValidationException(sprintf('Error validating %s: %s', $error['pointer'], $error['message']));
}

if (is_array($more) && count($more) > 0) {
$error += $more;
}

$this->errors[] = $error;
}

public function addErrors(array $errors)
{
if ($errors) {
$this->errors = array_merge($this->errors, $errors);
}
}

public function getErrors()
{
return $this->errors;
}

public function isValid()
{
return !$this->getErrors();
}





public function reset()
{
$this->errors = array();
}
}
