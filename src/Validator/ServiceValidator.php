<?php

namespace Swoft\Rpc\Server\Validator;

use Swoft\Bean\Annotation\Bean;
use Swoft\Bean\Annotation\ValidatorFrom;
use Swoft\Validator\AbstractValidator;

/**
 * Service Validator
 * @Bean()
 */
class ServiceValidator extends AbstractValidator
{
    /**
     * @param mixed $validators
     * @param array ...$params
     * @return mixed
     * @throws \ReflectionException
     * @throws \Swoft\Exception\ValidatorException
     */
    public function validate($validators, ...$params)
    {
        list($serviceHandler, $serviceData) = $params;
        $args = $this->getServiceArgs($serviceHandler, $serviceData);

        foreach ($validators ?? [] as $type => $validator) {
            if ($type !== ValidatorFrom::SERVICE) {
                continue;
            }
            $this->validateArg($args, $validator);
        }

        return;
    }


    /**
     * validate arg
     *
     * @param array $args
     * @param array $validator
     * @throws \Swoft\Exception\ValidatorException
     */
    public function validateArg(array $args, array $validator)
    {
        foreach ($validator as $name => $info) {
            if (! isset($args[$name])) {
                continue;
            }
            $this->doValidation($args[$name], $info);
        }
    }

    /**
     * get args of called function
     *
     * @param array $serviceHandler
     * @param array $serviceData
     * @return array
     * @throws \ReflectionException
     */
    private function getServiceArgs(array $serviceHandler, array $serviceData): array
    {
        list($className, $method) = $serviceHandler;
        $rc = new \ReflectionClass($className);
        $rm = $rc->getMethod($method);
        $mps = $rm->getParameters();
        $params = $serviceData['params'] ?? [];

        if (empty($params)) {
            return [];
        }

        $index = 0;
        $args = [];
        foreach ($mps as $mp) {
            $name = $mp->getName();
            if (! isset($params[$index])) {
                break;
            }
            $args[$name] = $params[$index];
            $index++;
        }

        return $args;
    }
}
