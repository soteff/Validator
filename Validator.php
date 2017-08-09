<?php
namespace App;

/**
 * Class Validator
 * @package App
 */
class Validator
{
    /**
     * @param $params
     * @param $rules
     * @return array
     */
    public static function validate($params, $rules)
    {
        $validParams = [];
        foreach ($rules as $field => $fieldRules) {
            // Skip rules if not required
            if (! in_array('required', $fieldRules, true) && ! isset($params[$field])) {
                continue;
            }

            foreach ($fieldRules as $rule) {
                $ruleParts = explode('|', $rule);
                // Validate rules
                $validateFuncParams = [$params, $field];
                $validateRuleParts = explode(':', $ruleParts[0]);
                if (isset($validateRuleParts[1])) {
                    $validateFuncParams[] = $validateRuleParts[1];
                }
                call_user_func_array('\App\Validator::validate' . ucfirst($validateRuleParts[0]), $validateFuncParams);

                // Sanitize param
                $sanitizeFunction = 'sanitize' .  ucfirst($ruleParts[0]);
                if (method_exists(self::class, $sanitizeFunction) && (! isset($ruleParts[1]) || $ruleParts[1] !== 'no-sanitize')) {
                    call_user_func_array('\App\Validator::' . $sanitizeFunction, [&$params, $field]);
                }
            }
            $validParams[$field] = $params[$field];
        }

        return $validParams;
    }

    /**
     * @param $params
     * @param $field
     * @throws ValidatorException
     */
    protected static function validateRequired($params, $field)
    {
        if (! isset($params[$field])) {
            throw new ValidatorException('Field ' . $field . ' is required!');
        }
    }

    /**
     * @param $params
     * @param $field
     * @throws ValidatorException
     */
    protected static function validateInt($params, $field)
    {
        if (filter_var($params[$field], FILTER_VALIDATE_INT) === false) {
            throw new ValidatorException('Field ' . $field . ' is not integer!');
        }
    }

    /**
     * @param $params
     * @param $field
     * @param bool $valuesType
     * @throws ValidatorException
     */
    protected static function validateArray($params, $field, $valuesType = false)
    {
        if (! is_array($params[$field])) {
            throw new ValidatorException('Field ' . $field . ' is not array!');
        }

        // Validate values
        if ($valuesType === 'int') {
            foreach ($params[$field] as $value) {
                if (! filter_var($value, FILTER_VALIDATE_INT)) {
                    throw new ValidatorException('Field ' . $field . ' value ' . $value . ' is not integer!');
                }
            }
        }
    }

    /**
     * @param $params
     * @param $field
     * @throws ValidatorException
     */
    protected static function validateString($params, $field)
    {
        if (! is_string($params[$field])) {
            throw new ValidatorException('Field ' . $field . ' is not string!');
        }
    }

    /**
     * @param $params
     * @param $field
     * @throws ValidatorException
     */
    protected static function validateEmail($params, $field)
    {
        if (! filter_var($params[$field], FILTER_VALIDATE_EMAIL)) {
            throw new ValidatorException('Field ' . $field . ' is not a valid email!');
        }
    }

    /**
     * @param $params
     * @param $field
     * @throws ValidatorException
     */
    protected static function validateDate($params, $field)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $params[$field]);
        if (! $date || $date->format('Y-m-d') !== $params[$field]) {
            throw new ValidatorException('Field ' . $field . ' is not a valid date!');
        }
    }

    /**
     * @param $params
     * @param $field
     * @param $length
     * @throws ValidatorException
     */
    protected static function validateMinLength($params, $field, $length)
    {
        if (mb_strlen($params[$field], 'UTF-8') < $length) {
            throw new ValidatorException('Field ' . $field . ' must be more than ' . $length . ' chars!');
        }
    }

    /**
     * @param $params
     * @param $field
     * @param $length
     * @throws ValidatorException
     */
    protected static function validateMaxLength($params, $field, $length)
    {
        if (mb_strlen($params[$field], 'UTF-8') > $length) {
            throw new ValidatorException('Field ' . $field . ' must be less than ' . $length . ' chars!');
        }
    }

    /**
     * @param $params
     * @param $field
     */
    protected static function sanitizeInt(&$params, $field)
    {
        $params[$field] = (int) filter_var($params[$field], FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @param $params
     * @param $field
     */
    protected static function sanitizeString(&$params, $field)
    {
        $params[$field] = filter_var(trim($params[$field]), FILTER_SANITIZE_STRING);
    }

    /**
     * @param $params
     * @param $field
     */
    protected static function sanitizeEmail(&$params, $field)
    {
        $params[$field] = filter_var(trim($params[$field]), FILTER_SANITIZE_EMAIL);
    }
}

class ValidatorException extends \Exception
{}