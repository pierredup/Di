<?php
/*
 * This file is part of the Di package.
 *
 * (c) 2014 Pierre du Plessis
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Di
{
    const PARAM = 512;
    const NEW_INSTANCE = 1024;
    const DEEP = 2048;

    /**
     * @var array
     */
    private static $instances = array();

    /**
     * @var array
     */
    private static $map = array();

    /**
     * Clears the current mapping and instances
     *
     * @static
     */
    public static function clear()
    {
        self::$map = array();
        self::$instances = array();
    }

    /**
     * Gets an instance of a class, or a parameter value.
     *
     * @param string $object
     * @param int    $flags
     *
     * @return mixed
     * @static
     */
    public static function get($object, $flags = 0)
    {
        if ($flags & self::PARAM) {
            return self::getParameter($object);
        }

        if (isset(self::$instances[$object]) && !($flags & (self::NEW_INSTANCE | self::DEEP))) {
            return self::$instances[$object];
        }

        if ($flags & (self::NEW_INSTANCE & ~self::DEEP)) {
            $flags &= !self::NEW_INSTANCE;
        }

        return self::$instances[$object] = self::getClassInstance($object, $flags);
    }

    /**
     * Maps values for use when injecting variables
     *
     * @param      $key
     * @param null $value
     *
     * @static
     */
    public static function map($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                self::map($k, $v);
            }
        }

        if (is_scalar($key) && !empty($value)) {
            self::$map[$key] = $value;
        }
    }

    /**
     * Gets a new instance of a class
     *
     * @param     $class
     * @param int $flags
     *
     * @return object
     * @static
     */
    protected static function getClassInstance($class, $flags = 0)
    {
        $reflection = new ReflectionClass($class);

        $parameters = self::getClassParameters($reflection, $flags);

        return $reflection->newInstanceArgs($parameters);
    }

    /**
     * @param ReflectionClass $reflection
     * @param int             $flags
     *
     * @return array
     * @static
     */
    protected static function getClassParameters(ReflectionClass $reflection, $flags = 0)
    {
        $parameters = array();

        $constructor = $reflection->getConstructor();

        if (null !== $constructor && $constructor->getNumberOfParameters() > 0) {
            foreach ($constructor->getParameters() as $parameter) {
                $parameters[] = self::getParameterValue($parameter, $flags);
            }
        }

        return $parameters;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param int                 $flags
     *
     * @throws InvalidArgumentException
     * @return mixed
     * @static
     */
    protected static function getParameterValue(ReflectionParameter $parameter, $flags = 0)
    {
        $name = $parameter->getName();

        if (isset(self::$map[$name])) {
            return self::getParameter($name);
        }

        if ($parameter->getClass() instanceof ReflectionClass) {
            $class = $parameter->getClass()->getName();

            if (isset(self::$map[$class])) {
                return self::$map[$class];
            }

            return self::get($class, $flags);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        return null;
    }

    /**
     * Gets a parameter value
     *
     * @param $name
     *
     * @return mixed
     * @static
     * @throws InvalidArgumentException
     */
    protected static function getParameter($name)
    {
        if (isset(self::$map[$name])) {
            $param = self::$map[$name];

            if ($param instanceof Closure || (is_array($param) && is_callable($param))) {
                $param = call_user_func($param);
                self::$map[$name] = $param;
            }

            return $param;
        }

        throw new InvalidArgumentException(sprintf('Parameter %s does not exist', $name));
    }
}
