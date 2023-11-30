<?php
namespace RamaCan\ImageKit;

use ImageKit\ImageKit;
use League\Flysystem\UnableToReadFile;
use RamaCan\ImageKitAdapter\Exceptions\ImageKitConfigurationException;

class ImageKitAdapter
{
    protected $imageKit;
    protected $options;

    public function __construct(Imagekit $imageKit, $options = [])
    {
        $this->imageKit = $imageKit;
    }

    public function getimageKit(): Imagekit
    {
        return $this->imageKit;
    }

    public function upload(string $path, $contents)
    {
      $location = $this->getFileFolderNames($path);

        if($location === false)
            return false;

        if(!is_resource($contents) && !filter_var($contents, FILTER_VALIDATE_URL))
            $contents = base64_encode($contents);

        $uploadResult = $this->imageKit->upload([
            'file'              => $contents,
            'fileName'          => $path,
            'useUniqueFileName' => false,
            'folder'            => $location['directory'] ?? '/',
        ]); 

        return $uploadResult->result->url ?? null;
    }

    public function delete(string $path): void
    {

        if(!strlen($path))
            throw new UnableToReadFile('Path should not be empty.');

        $file = $this->searchFile($path);

        // Make a purge cache request
        if(isset($this->options['purge_cache_update']) && $this->options['purge_cache_update']['enabled'] === true) {
            
            if(!isset($this->options['purge_cache_update']['endpoint_url']))
                throw new ImageKitConfigurationException('Purge cache option is enabled, but endpoint url is not set.');

            $this->imageKit->purgeCache($this->options['purge_cache_update']['endpoint_url'].'/'.$path);
        
        }

        $this->imageKit->deleteFile($file->fileId);
        
    }

    public function searchFile($path) 
    {

        $location = $this->getFileFolderNames($path);

        // Get file from old path
        $file = $this->imageKit->listFiles([
            'name'          => $location['file'] ?? '',
            'path'          => $location['directory'] ?? '',
            'includeFolder' => true
        ]);

        if(empty($file->success))
            throw new UnableToReadFile('File or directory not found.');

        return $file->success[0];

    }

    public function getFileFolderNames(string $path) 
    {

        if(!$path)
            return false;

        $folder = '/';
        $fileName = $path;

        // Check for folders in path (file name)
        $folders = explode('/', $path);
        if(count($folders) > 1) {
            $fileName = end($folders);
            $folder = str_replace('/'.end($folders), '', $path);
        }

        return [
            'file'  => $fileName,
            'directory' => $folder
        ];
    }
}