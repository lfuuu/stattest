<?php

namespace app\classes\uu\forms;

/*
 * 
 * Интефейс который реализуют классы поведения формы по дефолту, 
 * 
 * 
 *  */

interface TariffFomInterface {
    
    public function getModel();
    
    public function getTariffPeriods();
    
    public function getTariffResources();
}