<?php declare(strict_types=1);
/**
 * @package LuxLig Base
 * @author Nuri <truongdoba.nuri@gmail.com>
 * Copyright © 2024 Luxury Lighting.
 */

namespace LuxLig\Base\Model;

class Registry
{
    /**
     * Registry collection
     *
     * @var array
     */
    private array $data = [];

    /**
     * Retrieve a value from registry by a key
     *
     * @param string $key
     * @return mixed
     */
    public function getData(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Register a new variable
     *
     * @param string $key
     * @param mixed $value
     */
    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Unregister a variable from register by key
     *
     * @param string $key
     * @return void
     */
    public function unsetData(string $key): void
    {
        if (isset($this->data[$key])) {
            if (is_object($this->data[$key])
                && method_exists($this->data[$key], '__destruct')
                && is_callable([$this->data[$key], '__destruct'])
            ) {
                $this->data[$key]->__destruct();
            }

            unset($this->data[$key]);
        }
    }

    /**
     * Destruct registry items
     */
    public function __destruct()
    {
        $keys = array_keys($this->data);
        array_walk($keys, [$this, 'unsetData']);
    }
}
