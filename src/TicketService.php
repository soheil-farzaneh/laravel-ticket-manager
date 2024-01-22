<?php

namespace soheilfarzaneh\Ticket;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use soheilfarzaneh\Ticket\Contracts\InterfaceTicketService;
use soheilfarzaneh\Ticket\Exceptions\TicketClosedException;


class TicketService implements InterfaceTicketService
{
    private const CONFIG_PATH = 'Handy.ticket';

    private $statusFeild;

    private $tokenName;

    private $createByFeild;

    private $userIdFeild;

    private $fileFeild;

    private $attachmentPath;

    public $ticketModel;

    private $data;

    private $replyInfo;

    public function __construct()
    {
        $this->statusFeild = $this->getConfig('fields.status');
        $this->tokenName = $this->getConfig('token_name');
        $this->userIdFeild = $this->getConfig('fields.user_id', 'user_id');
        $this->fileFeild = $this->getConfig('fields.files', 'files');
        $this->attachmentPath = $this->getConfig('attachment_path');
        $modelNamespace = $this->getConfig('model', Ticket::class);
        $this->ticketModel = new $modelNamespace();
    }

    public function createTicket($request)
    {
        try {
            $this->data = $this->prepareData($request, 'fields');
            $this->ticketModel = $this->ticketModel->registerTicket($this->data);
            $this->replyToTicket($request, 'waiting');

            return $this->ticketModel;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function updateTicket(array $data)
    {
        try {
            return $this->ticketModel->updateTicketFeilds($data);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function replyToTicket($request, $status = null)
    {
        try {
            $this->data = $this->prepareData($request, 'reply.fields', true);
            $this->proccessReplyTicket('registerReply', $status);

            return $this->replyInfo;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function updateReply($request, int $replyId)
    {
        try {
            $this->data = $this->prepareData($request, 'reply.fields', true);
            $replyModel = $this->loadReplyById($replyId);
            $this->proccessReplyTicket('updateReplyFeilds', null, $replyModel);

            return $this->replyInfo;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function proccessReplyTicket($actionMethod, $status, $replyModel = null)
    {
        $this->getCreatedBy();
        if ($this->data['created_by'] != 'admin') {
            $this->checkClosedTicket();
        }
        $status = $this->setTicketStatus($status);
        $this->updateTicket([$this->statusFeild => $status]);
        $replyValues = array_merge($this->getCommonData(), [
            'text' => $this->data['text'],
        ]);

        $this->replyInfo = ($replyModel) ? $this->ticketModel->$actionMethod($replyModel, $replyValues) :
                                            $this->ticketModel->$actionMethod($replyValues);

        if (! empty($this->data['files'])) {
            $this->updateAndSaveFilesIfExist();
        }
    }

    protected function updateAndSaveFilesIfExist()
    {
        $replyId = $this->replyInfo->id;
        $ticketId = $this->replyInfo->ticket_id;
        try {
            $existingFiles = $this->ticketModel->getFilesWithPranetId($replyId);

            $filesToKeep = [];
            $dataToInsert = [];
            $filesToDelete = [];

            foreach ($this->data['files'] as $file) {
                $filePath = "{$this->attachmentPath}/".\Auth::id().'/'.$file;
                if (! \Storage::exists($filePath)) {
                    throw new DontUploadFileException();
                }
                if (! $existingFiles->contains($filePath)) {
                    $dataToInsert[] = array_merge($this->getCommonData(), [
                        'parent_id' => $replyId,
                        'file' => $filePath,
                        'ticket_id' => $ticketId,
                    ]);
                } else {
                    $filesToKeep[] = $filePath;
                }
            }

            if (! empty($dataToInsert)) {
                $this->saveFiles($dataToInsert);
            }

            $this->deleteFiles($existingFiles, $filesToKeep);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    protected function saveFiles(array $dataToInsert)
    {
        $this->ticketModel->saveFiles($dataToInsert);
    }

    protected function deleteFiles($existingFiles, $filesToKeep)
    {
        $filesToDelete = $existingFiles->filter(function ($file) use ($filesToKeep) {
            return ! in_array($file, $filesToKeep);
        });

        if ($filesToDelete->isNotEmpty()) {
            $this->replyInfo->whereIn('id', array_keys($filesToDelete->toArray()))
                ->delete();
        }
    }

    public function loadTicketById(int $id)
    {
        try {
            $this->ticketModel = $this->ticketModel->getTicketById($id);

            return $this;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function loadReplyById(int $id)
    {
        try {
            $replyModel = $this->ticketModel->getReplyById($id);

            return $replyModel;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function loadAllTickets()
    {
        $response = $this->ticketModel->allTickets();

        return $response;
    }

    protected function prepareData($request, string $configKey, $isReply = false)
    {
        $fieldsConfig = $this->getConfig($configKey);
        $data = $request->only(array_keys($fieldsConfig));

        $data['ip'] = $request->ip();
        $authenticatedUserId = \Auth::id();

        if ($isReply) {

            $data[$this->userIdFeild] = $authenticatedUserId;
            $data[$this->fileFeild] = isset($data[$this->fileFeild]) ? $data[$this->fileFeild] : null;
        } else {
            $data[$this->userIdFeild] = $data[$this->userIdFeild] ?? $authenticatedUserId;
        }

        return $data;
    }

    private function setTicketStatus($status)
    {
        try {
            if ($status) {
                $status = ($this->data['created_by'] == 'admin') ? 'adminCreated' : $status;
            } else {
                $status = ($this->data['created_by'] == 'admin') ? 'answered' : 'waiting';
            }

            return $status;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function getCreatedBy()
    {
        $user = \Auth::user();
        $this->data['created_by'] = ($user && $user->token()->name === $this->tokenName) ? 'admin' : 'user';

        return $this;
    }

    private function checkClosedTicket()
    {
        if ($this->ticketModel->{$this->statusFeild} == 'closed') {
            throw new TicketClosedException();
        }
    }

    private function getCommonData()
    {
        $commonData = [
            'ip' => $this->data['ip'],
            'user_id' => $this->data['user_id'],
            'created_by' => $this->data['created_by'],
        ];

        return $commonData;
    }

    protected function getConfig($key, $default = null)
    {
        return config(self::CONFIG_PATH.".$key", $default);
    }

    public function uploader(UploadedFile $file, int $localId)
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = "{$name}.{$localId}.{$file->getClientOriginalExtension()}";
        $file->storeAs("{$this->attachmentPath}/".\Auth::id(), $name);

        return $name;
    }
}