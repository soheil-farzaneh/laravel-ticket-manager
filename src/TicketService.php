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


class TicketService implements InterfaceTicketService {

    private const CONFIG_PATH = 'Handy.ticket';

    private $statusFeild;
    private $tokenName;
    private $createByFeild;
    private $userIdFeild;
    private $fileFeild;
    private $attachmentPath;
    public  $ticketModel;
    private $data;

    public function __construct() {

        $this->statusFeild      = $this->getConfig('fields.status');
        $this->tokenName        = $this->getConfig('token_name');
        $this->userIdFeild      = $this->getConfig('fields.user_id' , 'user_id');
        $this->fileFeild        = $this->getConfig('fields.file' , 'file');
        $this->attachmentPath   = $this->getConfig('attachment_path');
        $modelNamespace         = $this->getConfig('model' , Ticket::class);
        $this->ticketModel      = new $modelNamespace();
    }

    public function createTicket($request) {

        try {

            $this->data = $this->prepareData($request , 'fields');

            $this->ticketModel = $this->ticketModel->registerTicket($this->data);

            $this->replyToTicket($request , 'waiting');

            return $this->ticketModel;

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function replyToTicket($request , $status = null) {

        try {

            $ticketId = $this->ticketModel->id;
            $this->data = $this->prepareData($request , 'reply.fields' , true , $ticketId);

            $this->checkUserRole();

            if ($this->data['created_by'] != 'admin') {
                $this->checkClosedTicket();
            }

            $status = $this->setTicketStatus($status);

            $this->updateTicket([$this->statusFeild => $status]);

            return $this->ticketModel->registerReply($this->data);

        }catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function findTicketById($id) {

        try {

            $this->ticketModel = $this->ticketModel->getTicketById($id);
            return $this;

        }catch (\Exception $exception) {
            throw $exception;
        }    
    }

    public function findReplyById($id) {

        try {

            $replyModel = $this->ticketModel->getReplyById($id);
            return $replyModel;

        }catch (\Exception $exception) {
            throw $exception;
        }   
    }


    public function findAllTickets() {

        $response = $this->ticketModel->allTickets();
        return $response;
    }

    private function checkUserRole() {

        $user = \Auth::user();
        $this->data['created_by'] = ($user && $user->token()->name === $this->tokenName) ? 'admin' : 'user';
        return $this;
    }

    private function checkClosedTicket() {

        if ($this->ticketModel->{$this->statusFeild} == "closed") throw new TicketClosedException();
    }

    private function setTicketStatus($status) {

        try {
            if ($status) {
                $status = ($this->data['created_by'] == 'admin') ? 'adminCreated' : $status; 
            }else {
                $status = ($this->data['created_by'] == 'admin') ? 'answered' : 'waiting';
            }

            return $status;
        }catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function updateTicket(array $data) {

        try {
            return $this->ticketModel->updateTicketFeilds($data);

        }catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function updateReply($request , $replyId) {

        try {
            
            $data = $this->prepareData($request , 'reply.fields' , true);
            $replyModel = $this->loadReplyById($replyId);
            
            return $this->ticketModel->updateReplyFeilds($replyModel , $data);

        }catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    protected function prepareData($request  , $configKey , $isReply = false , $ticketId = null) {

        $fieldsConfig = $this->getConfig($configKey);
        $data = $request->only(array_keys($fieldsConfig));

        $data['ip']          = $request->ip();
        $authenticatedUserId = \Auth::id();
        
        if ($isReply) {

            $data[$this->userIdFeild]    = $authenticatedUserId;
            $data[$this->fileFeild]      = isset($data[$this->fileFeild]) ? $this->uploader($data[$this->fileFeild], $ticketId) : null;
        }else {
            $data[$this->userIdFeild] = $data[$this->userIdFeild] ?? $authenticatedUserId;
        }

        return $data;
    }

    protected function getConfig($key, $default = null) {

        return config(self::CONFIG_PATH . ".$key", $default);
    }

    protected function uploader(UploadedFile $file, $ticketId) {
        $name = rand(0, time()) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs("{$this->attachmentPath}/{$ticketId}", $name); 
    }
}