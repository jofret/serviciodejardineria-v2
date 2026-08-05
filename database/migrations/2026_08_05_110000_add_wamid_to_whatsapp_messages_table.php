<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            // ID de mensaje de WhatsApp (wamid): evita duplicar el mensaje si
            // Meta reintenta la entrega del webhook.
            $table->string('wamid')->nullable()->unique()->after('whatsapp_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropColumn('wamid');
        });
    }
};
