<?php

namespace App\Services\Helper;

use App\Http\Resources\Helper\FileResource;
use App\Models\Helper\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class FileService
{

    public function record($files, $entity)
    {
        $respond = [];
        foreach ($files AS $key => $value):
            $file_path = Str::uuid()->toString() . '.' . $value->getClientOriginalExtension();

            $value->move('uploads', $file_path);

            $one_respond = File::create([
                'user_uuid' => $entity['user_uuid'],
                'file_name' => $value->getClientOriginalName(),
                'file_path' => $file_path,
                'file_parent' => $entity['file_parent'],
            ]);

            $respond[] = $one_respond;
        endforeach;

        return $respond;
    }

    public function update($entity, $uuid)
    {
        File::where('uuid', $uuid)->update($entity);
    }

    public function delete($uuid)
    {
        File::where('uuid', $uuid)->update(['status' => Config::get('common.status.deleted')]);
    }

}