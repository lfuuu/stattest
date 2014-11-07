<?php
	/**
	 * Класс предназначен для добавления, взятия и удаления файлов
	 */
	class mailFiles
	{
		/**
		 * @var int $job_id ID задачи на отправку писем
		 */
		private $job_id;
		/**
		 * @var string $files_path путь к файлам текущей задачи на отправку писем
		 */
		private $files_path;
		/**
		 * Сохраняет ID задачи на отправку писем и путь к файлам этой задачи
		 * @param int $job_id - ID задачи на отправку писем
		 */
		public function __construct($job_id) 
		{
			$this->job_id = $job_id;
			$this->files_path = PATH_TO_ROOT.'store/mail/attachments/' . $job_id . '/';
			if (!file_exists($this->files_path) && is_writeable($this->files_path))
			{
				mkdir($this->files_path, 0777, true);
			}
		}
		/**
		 * Взятие всех файлов текущей задачи на отправку писем
		 */
		public function getFiles($with_paths = false) 
		{
			global $db;
			$files = $db->AllRecords('SELECT * 
						FROM mail_files 
						WHERE 
							job_id='.$this->job_id.' 
						ORDER BY id');
			if ($with_paths)
			{
				foreach ($files as &$v)
				{
					$v['path'] = $this->files_path.$v['id'].'.attach';
				}
			}
			return $files;
		}
		/**
		 * Добавляет новый файл в текущую задачу
		 * @param int $name - имя файла
		 */
		public function putFile()
		{
			global $db;

			if (!isset($_FILES['file']) || !$_FILES['file']['tmp_name']) return;

			$name = basename($_FILES['file']['name']);
			$type = $_FILES['file']['type'];
			if (move_uploaded_file($_FILES['file']['tmp_name'],$this->files_path.$id.'.attach'))
            {
                $V = array('name'=>$name,'job_id'=>$this->job_id,'type'=>$type);
                $id = $db->QueryInsert('mail_files',$V);
            }
			
		}
		/**
		 * Возвращает информацию о файле 
		 * @param int $fid - ID файла в базе данных
		 */
		public function getFile($fid) 
		{
			global $db;
			$f = $db->getRow('select * from mail_files where id='.$fid.' and job_id='.$this->job_id);
			if ($f) 
			{
				$f['path'] = $this->files_path.$f['id'].'.attach';
			}
			return $f;
		}
		/**
		 * Удаляет файл
		 * @param int $fid - ID файла в базе данных
		 */
		public function deleteFile($fid) 
		{
			global $db;
			if ($f = $this->getFile($fid)) 
			{
				$db->Query('delete from mail_files where id='.$f['id']);
				unlink($f['path']);
			}
		}
	}

?>
