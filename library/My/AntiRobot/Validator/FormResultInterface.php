<?php

namespace My\AntiRobot\Validator;

interface FormResultInterface {

    /**
     * 表单验证结果
     * @param bool $formValidResult
     * @return void
     */
    public function setFormValidResult($formValidResult);
}