<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// В файле class.php должен располагаться класс компонента унаследованный от CBitrixComponent.

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Page\Asset;

class ExampleCompSimple extends CBitrixComponent {
    private $_request;

    /**
     * Проверка наличия модулей требуемых для работы компонента
     * @return bool
     * @throws Exception
     */
    private function _checkModules() {
        if ( !Loader::includeModule('iblock') ) {
            throw new \Exception('Не загружены модули необходимые для работы модуля');
        }

        return true;
    }

    /**
     * Обертка над глобальной переменной
     * @return CAllMain|CMain
     */
    private function _app() {
        global $APPLICATION;
        return $APPLICATION;
    }

    /**
     * Обертка над глобальной переменной
     * @return CAllUser|CUser
     */
    private function _user() {
        global $USER;
        return $USER;
    }

    /**
     * Подключает скрипты из папки шаблона, за исключением script.js - подключается первым
     */
    public function connectJSFromFolder()
    {
        $folder = $this->__template->__folder .'/';
        // Скрипты которы надо подключить первыми
        $sortIndexNecessary = [
            'chunk-necessary.js',
        ];
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . $folder)) {
            $files = array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $folder), array('.', '..'));

            //Меняем сортировку подключаемых файлов, чтобы файлы vendor были подключены первыми
            $result = array();
            foreach ($sortIndexNecessary as $index => $sortPriorityItem) {
                if (in_array($sortPriorityItem, $files)) {
                    $result[$index] = $sortPriorityItem;
                }
            }
            $files = array_unique(array_merge($result, $files));
            
            foreach ($files as $file) {
                if(pathinfo($file)['extension'] == 'js'){
                    Asset::getInstance()->addJs($folder . $file, true);
                }
            }
        }
    }
    
    /**
     * Подключает стили из папки шаблона, за исключением style.css - подключается первым
     */
    public function connectStylesFromFolder()
    {
        $folder = $this->__template->__folder .'/';
        
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . $folder)) {
            $files = array_diff(scandir($_SERVER['DOCUMENT_ROOT'] . $folder), array('.', '..'));
            foreach ($files as $file) {
                if(pathinfo($file)['extension'] == 'css'){
                    Asset::getInstance()->addCss($folder . $file);
                };
            }
        }
    }
    
    /**
     * Подготовка параметров компонента
     * @param $arParams
     * @return mixed
     */
    public function onPrepareComponentParams($arParams) {
        // тут пишем логику обработки параметров, дополнение параметрами по умолчанию
        // и прочие нужные вещи
        return $arParams;
    }

    /**
     * Точка входа в компонент
     * Должна содержать только последовательность вызовов вспомогательых ф-ий и минимум логики
     * всю логику стараемся разносить по классам и методам 
     */
    public function executeComponent() {
        $this->_checkModules();

        $this->_request = Application::getInstance()->getContext()->getRequest();
        
        // что-то делаем и результаты работы помещаем в arResult, для передачи в шаблон
        $this->arResult['SOME_VAR'] = 'some result data for template';

        $this->includeComponentTemplate();
        
        //Подключаем дополнительные стили и скрипты
        $this->connectStylesFromFolder();
        $this->connectJSFromFolder();
    }
}