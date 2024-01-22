<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function __construct()
    {
        $this->ticketsTable = config('support.ticket.table');
        $this->repliesTable = config('support.ticket.reply.table');
    }

    public function up()
    {
        Schema::create($this->ticketsTable, function (Blueprint $table) {
            $table->id();
            $table->string('title');  
            $table->enum('department' , ['financial' , 'general' , 'technical']);
            $table->enum('priority' , ['low' , 'medium' , 'high']);
            $table->enum('status' , ['waiting' , 'pending' , 'answered' , 'closed' , 'customerResponse']);
            $table->enum('satisfaction' , ['happy' , 'unhappy'])->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->ipAddress('ip')->nullable();
            $table->string('opts');
            $table->timestamps('deleted_at');
            $table->timestamps();
        });

        Schema::create($this->repliesTable, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('ticket_id');
            $table->foreign('ticket_id')->on('tickets')->references('id')->onDelete('cascade');
            $table->longText('text')->nullable();
            $table->string('ip')->nullable();
            $table->string('file')->nullable();
            $table->string('created_by');
            $table->timestamps('deleted_at');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->repliesTable);
        Schema::dropIfExists($this->ticketsTable);
    }
};
