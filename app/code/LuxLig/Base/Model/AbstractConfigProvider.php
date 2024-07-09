<?php declare(strict_types=1);
/**
 * @package LuxLig Theme
 * @author Nuri <truongdoba.nuri@gmail.com>
 * Copyright © 2024 Luxury Lighting.
 */

namespace LuxLig\Base\Model;

use Exception;
use LogicException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractConfigProvider
{
    /**
     * xpath prefix of module (section)
     * @var string '{section}/'
     */
    protected string $pathPrefix = '/';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * Stored values by scopes
     *
     * @var array
     */
    protected array $data;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Json
     */
    protected Json $json;

    public function __construct(
        ScopeConfigInterface  $scopeConfig,
        Json                  $json,
        StoreManagerInterface $storeManager,
        array                 $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->data = $data;

        if ($this->pathPrefix === '/') {
            throw new LogicException('$pathPrefix should be declared');
        }
    }

    /**
     * clear local storage
     *
     * @return void
     */
    public function clean(): void
    {
        $this->data = [];
    }

    /**
     * @return mixed|null
     */
    public function getDataObject(): mixed
    {
        return $this->data['object'] ?? null;
    }

    /**
     * Get store by ID
     *
     * @param int|string|null $storeId
     * @return StoreInterface|null
     */
    public function getStoreById(int|string $storeId = null): ?StoreInterface
    {
        $store = null;
        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (Exception $e) {
        }

        return $store ?? null;
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getConfigByCurrentStore(string $path): mixed
    {
        return $this->getValue(
            $path,
            $this->getCurrentStoreId()
        );
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $path '{group}/{field}'
     * @param int|ScopeInterface|null $storeId Scope code
     * @param string $scope
     *
     * @return mixed
     */
    protected function getValue(
        string             $path,
        ScopeInterface|int $storeId = null,
        string             $scope = ScopeInterface::SCOPE_STORE
    ): mixed
    {
        if ($storeId === null && $scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            return $this->scopeConfig->getValue($this->pathPrefix . $path, $scope, $storeId);
        }

        if ($storeId instanceof \Magento\Framework\App\ScopeInterface) {
            $storeId = $storeId->getId();
        }
        $scopeKey = $storeId . $scope;
        if (!isset($this->data[$this->pathPrefix . $path]) || !array_key_exists($scopeKey, $this->data[$this->pathPrefix . $path])) {
            $this->data[$this->pathPrefix . $path][$scopeKey] = $this->scopeConfig->getValue($this->pathPrefix . $path, $scope, $storeId);
        }

        return $this->data[$this->pathPrefix . $path][$scopeKey];
    }

    /**
     * @return int|null
     */
    public function getCurrentStoreId(): ?int
    {
        $storeId = null;
        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
        } catch (Exception $e) {
        }

        return $storeId ?? null;
    }

    /**
     * Convert multiline text into array.
     *
     * @param string $fieldValue
     * @return array
     */
    public function splitTextareaValueByLine(string $fieldValue): array
    {
        $lines = explode(PHP_EOL, $fieldValue);

        if (empty($lines)) {
            return [];
        }

        return array_filter(array_map('trim', $lines));
    }

    /**
     * Convert multiselect value into array.
     *
     * @param string $value
     * @param bool $clearEmpty
     * @return array
     */
    public function prepareMultiselectValue(string $value, bool $clearEmpty = true): array
    {
        $values = explode(',', $value);
        return $clearEmpty ? array_filter($values) : $values;
    }

    /**
     * @param string|null $value
     * @param bool $clearEmpty
     * @return array
     */
    public function prepareJsonObjectValue(?string $value, bool $clearEmpty = true): array
    {
        $result = [];

        if (!empty($value)) {
            $result = $this->json->unserialize($value) ?? [];
        }
        return $clearEmpty ? array_filter($result) : $result;
    }

    /**
     * @param $path
     * @param int|string|null $storeId
     *
     * @return mixed
     */
    public function getMagentoConfig($path, int|string $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * An alias for scope config with scope type Default
     *
     * @param string $path '{group}/{field}'
     *
     * @return mixed
     */
    protected function getGlobalValue(string $path): mixed
    {
        return $this->getValue($this->pathPrefix . $path, null, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @param string $path '{group}/{field}'
     *
     * @return bool
     */
    protected function isSetGlobalFlag(string $path): bool
    {
        return $this->isSetFlag($this->pathPrefix . $path, null, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @param string $path '{group}/{field}'
     * @param int|ScopeInterface|null $storeId
     * @param string $scope
     *
     * @return bool
     */
    protected function isSetFlag(
        string             $path,
        int|ScopeInterface $storeId = null,
        string             $scope = ScopeInterface::SCOPE_STORE
    ): bool
    {
        return (bool)$this->getValue($this->pathPrefix . $path, $storeId, $scope);
    }
}
