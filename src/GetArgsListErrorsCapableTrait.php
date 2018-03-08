<?php

namespace Dhii\Validation;

use OutOfRangeException;
use ReflectionParameter;
use Exception as RootException;
use Dhii\Util\String\StringableInterface as Stringable;

trait GetArgsListErrorsCapableTrait
{
    protected function _getArgsListErrors($args, $spec)
    {
        $errors = [];

        foreach ($spec as $_idx => $_param) {
            if (!($_param instanceof ReflectionParameter)) {
                throw $this->_createOutOfRangeException($this->__('Parameter #%1$d of the specification is invalid', [$_idx]), null, null, $_param);
            }

            $pos          = $_param->getPosition(); // 0-based position index of the arg.
            $isArgPresent = key_exists($pos, $args); // Whether this arg is specified
            $isNullable   = $_param->allowsNull(); // Whether null is allowed

            // Is argument required but not present?
            if (!$_param->isOptional() && !$isArgPresent) {
                $errors[] = $this->__('Argument #%1$s is required', [$pos]);
                continue;
            }

            $arg      = $isArgPresent ? $args[$pos] : null; // The value of the arg
            $isNullOk = $isNullable && $isArgPresent && is_null($arg); // Argument is present, is null, and this is allowed

            // Is argument of the right type?
            if (method_exists($_param, 'hasType') && $_param->hasType()) {
                $type     = $_param->getType();
                $typeName = $type->getName();
                $isOfType = null;

                // If type is built-in, it should be safe to use a built-in function
                if ($type->isBuiltin()) {
                    $testFunc = sprintf('is_%1$s', $typeName);
                    $isOfType = call_user_func_array($testFunc, [$args[$pos]]);
                }
                // If type is not built-in, then check whether instance of
                else {
                    $isOfType = $arg instanceof $typeName;
                }

                if (!$isOfType && !$isNullOk) {
                    $errors[] = $this->__('Argument #%1$s must be of type "%2$s"', [$pos, $typeName]);
                    continue;
                }
            }
        }

        return $errors;
    }

    /**
     * Creates a new Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The value that is out of range, if any.
     *
     * @return OutOfRangeException The new exception.
     */
    abstract protected function _createOutOfRangeException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
