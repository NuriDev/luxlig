<?php
/**
 * @package LuxLig Theme
 * @author Nuri <truongdoba.nuri@gmail.com>
 * Copyright © 2024 Nuri.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::THEME,
    'frontend/LuxLig/core',
    __DIR__
);
