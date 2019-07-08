<?php
    class IniFileCRUD {
        private $fileName;
        //Массив с содержимым файла.
        private $fileArray;

        public function __construct($fileName) {
            $this->fileName = $fileName.".ini";
            //Если файла не существует, создаем новый.
            if (!file_exists($this->fileName)) {
                $this->createFile();
            }

            $this->fileArray = $this->readFileArray();
        }

        private function createFile() {
            $file = fopen($this->fileName, 'w');

            fclose($file);
        }

        private function readFileArray() {
            //Читаем содержимое файла в массив, включая разделы.
            $fileArray = parse_ini_file($this->fileName, true);

            return $fileArray;
        }

        public function getFileArray() {
            return $this->fileArray;
        }

        public function readSection($sectionName) {
            $section = null;

            foreach ($this->fileArray as $key => $value) {
                //Находим нужный раздел в общем массиве.
                if ($key == $sectionName) {
                    $section = $this->fileArray[$key];
                    break;
                }
            }

            if ($section == null) {
                echo 'Раздел не существует.';
            }

            return $section;
        }

        public function readValue($sectionName, $key) {
            $section = $this->readSection($sectionName);
            $values = array();

            if ($section == null) {
                return $values;
            }

            foreach ($section as $key_ => $value) {
                //Находим нужное значение в секции, значений может быть несколько (массив).
                if ($key_ == $key) {
                    array_push($values, $section[$key]);
                }
            }

            if (count($values) == 0) {
                echo 'Ключ отсутствует.';
            }

            return $values;
        }

        public function createSection($sectionName, $key, $value) {
            if ($this->readSection($sectionName) != null) {
                echo 'Раздел уже существует.';
                return;
            }
            //Создаем новый раздел
            $this->fileArray[$sectionName][$key] = $value;
        }

        public function createKeyValue($sectionName, $key, $newValue) {
            //Добавляем в раздел новую пару ключ-значение.
            $this->fileArray[$sectionName][$key] = $newValue;
        }

        public function writeFile() {
            //Открываем файд на запись.
            $writeFile = fopen($this->fileName, 'w');
            //Записываем обновленный общий массив.
            fwrite($writeFile, $this->build_ini_string($this->fileArray));
            //Закрываем файл.
            fclose($writeFile);
        }

        public function writeSection($sectionName, $newSection) {
            if ($this->readSection($sectionName) == null) {
                return;
            }
            //Перезаписываем раздел новым содержимым.
            $this->fileArray[$sectionName] = $newSection;
        }

        public function writeValue($sectionName, $key, $newValue) {
            //Существует ли ключ?
            if (count($this->readValue($sectionName, $key)) == 0) {
                return;
            }
            //Перезаписываем значение, по заданному ключу.
            $this->fileArray[$sectionName][$key] = $newValue;
        }

        public function deleteFile() {
            if (!file_exists($this->fileName)) {
                echo 'Файл не существует.';
                return;
            }
            //Удаляем файл.
            unlink($this->fileName);
        }

        public function deleteSection($sectionName) {
            if ($this->readSection($sectionName) == null) {
                return;
            }
            //Удаляем раздел из общего массива.
            unset($this->fileArray[$sectionName]);
        }

        public function deleteKeyValue($sectionName, $key) {
            //Существует ли такой ключ?
            if (count($this->readValue($sectionName, $key)) == 0) {
                return;
            }
            //Удаляем из общего массива пару ключ-значение.
            unset($this->fileArray[$sectionName][$key]);
        }

        //https://stackoverflow.com/questions/17316873/convert-array-to-an-ini-file
        //Преобразовываем массив обратно в *.ini файл.
        function build_ini_string(array $a) {
            $out = '';
            $sectionless = '';
            foreach($a as $rootkey => $rootvalue){
                if(is_array($rootvalue)){
                    //Определяем является ли раздел ассоциативным или индексированным массивом
                    $indexed_root = array_keys($rootvalue) == range(0, count($rootvalue) - 1);
                    //Записываем заголовок раздела
                    $out.= PHP_EOL."[$rootkey]".PHP_EOL;
                    foreach($rootvalue as $key => $value){
                        if(is_array($value)){
                            $indexed_item = array_keys($value) == range(0, count($value) - 1);

                            foreach($value as $subkey=>$subvalue){
                                if($indexed_item) $subkey = "";
                                $out.= "{$key}[$subkey] = $subvalue".PHP_EOL;
                            }
                        } else {
                            if($indexed_root){
                                //Записываем индексированные массивы
                                $sectionless .= "{$rootkey}[] = $value".PHP_EOL;
                            } else {
                                //записываем пары в подразделе.
                                $out.= "$key = $value".PHP_EOL;
                            }
                        }
                    }

                } else {
                    //записываем пары в корневом разделе.
                    $sectionless .= "$rootkey = $rootvalue" . PHP_EOL;
                }
            }
            return $sectionless.$out;
        }
 }

    //Тест

    $crud = new IniFileCRUD('testfile');


    $crud->createSection('new_section', 'newsection', 'newvalue');
    $crud->createKeyValue('new_section', 'key', 'value');
    $crud->createKeyValue('new_section', '1', 'value1');
    $crud->createKeyValue('new_section', '2', 'value2');
    $crud->createKeyValue('new_section', '3', 'value3');
    $crud->deleteKeyValue('new_section', '2');
    $crud->writeFile();
?>