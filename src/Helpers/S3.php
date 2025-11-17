<?php

namespace App\Helpers;

use Aws\S3\S3Client;

class S3
{

    private $s3Client;

    private $bucketName;

    public function __construct()
    {

        $this->bucketName = $_ENV['S3_BUCKET'];

        $this->s3Client = new S3Client([
            'region' => $_ENV['S3_REGION'],
            'credentials' => [
                'key' => $_ENV['S3_KEY'],
                'secret' => $_ENV['S3_SECRET'],
            ],
        ]);
    }

    public function put($args)
    {

        try {

            $args['Bucket'] = $this->bucketName;

            $this->s3Client->putObject($args);

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function doesObjectExist($key){

        try{

            $result = $this->s3Client->doesObjectExist($this->bucketName, $key);

            return $result;

        }catch (\Exception $exception) {
            return false;
        }
    }

    public function get($args)
    {

        try {

            $args['Bucket'] = $this->bucketName;

            $result = $this->s3Client->getObject($args);

            // Cast as a string
            $bodyAsString = (string) $result['Body'];

            // or call __toString directly
            $bodyAsString = $result['Body']->__toString();

            return $bodyAsString;

        } catch (\Exception $exception) {

            return false;
        }
    }

    public function delete($args)
    {

        try {

            $args['Bucket'] = $this->bucketName;

            $this->s3Client->deleteObject($args);

            return true;

        } catch (\Exception $exception) {

            return false;
        }
    }

    public function deleteDir($args)
    {

        try {

            $args['Bucket'] = $this->bucketName;

            $this->s3Client->deleteMatchingObjects($args['Bucket'], $args['Key']);

            return true;

        } catch (\Exception $exception) {

            return false;
        }
    }

    public function getUrl($key)
    {

        try {

            return $this->s3Client->getObjectUrl($this->bucketName, $key);

        } catch (\Exception $exception) {

            return false;
        }
    }

    public function getPresignedUrl($key, $minutes=5)
    {

        try {

            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            $request = $this->s3Client->createPresignedRequest($cmd, '+'.$minutes.' minutes');
            $presignedUrl = (string) $request->getUri();
            return $presignedUrl;

        } catch (\Exception $exception) {

            // file_put_contents(__DIR__.'/./ex.txt', $exception);

            return false;
        }
    }

    public function list()
    {

        try {

            $cmd = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucketName,
            ]);

            $objs = [];

            foreach ($cmd['Contents'] as $item) {

                $key = $item['Key'];
                $LastModified = !empty($item['LastModified']) ? $item['LastModified'] : null;
                $dateFormat = !empty($LastModified) ? $LastModified->format(\DateTime::ISO8601) : null;
                $date = null;
                if (!empty($dateFormat)) {
                    $date = date('d-m-Y',strtotime(explode("T", $dateFormat)[0]));
                }

                $objs[] = [
                    'Key' => $key,
                    'Size' => $this->convertToReadableSize($item['Size']),
                    'Date' => $date,
                    'Url' => $this->getUrl($key)
                ];
            }

            return $objs;

        } catch (\Exception $exception) {

            return false;
        }
    }

    function convertToReadableSize($size){
        $base = log($size) / log(1024);
        $suffix = array("", "KB", "MB", "GB", "TB");
        $f_base = floor($base);
        return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
      }
}
