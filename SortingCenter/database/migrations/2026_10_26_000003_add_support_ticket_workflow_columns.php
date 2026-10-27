<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->string('contact_email', 150)->nullable()->after('related_reference');
            $table->string('contact_phone', 20)->nullable()->after('contact_email');
            $table->text('resolution')->nullable()->after('status');
            $table->timestamp('closed_at')->nullable()->after('resolution');
            $table->timestamp('last_replied_at')->nullable()->after('closed_at');
        });

        Schema::table('issue_report_replies', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->default(1)->index()->after('id');
            $table->unsignedBigInteger('user_id')->nullable()->after('author_role');
            $table->string('author_name')->nullable()->after('user_id');
            $table->boolean('is_internal')->default(false)->after('body');
        });

        Schema::table('issue_report_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->default(1)->index()->after('id');
            $table->string('mime_type')->nullable()->after('original_name');
        });

        if (Schema::hasColumn('issue_report_attachments', 'mime')) {
            Schema::table('issue_report_attachments', function (Blueprint $table) {
                $table->renameColumn('mime', 'mime_type_legacy');
            });
        }

        if (! Schema::hasColumn('parcels', 'cod_remitted_at')) {
            Schema::table('parcels', function (Blueprint $table) {
                $table->timestamp('cod_remitted_at')->nullable()->after('cod_remitted');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('parcels', 'cod_remitted_at')) {
            Schema::table('parcels', function (Blueprint $table) {
                $table->dropColumn('cod_remitted_at');
            });
        }

        Schema::table('issue_report_attachments', function (Blueprint $table) {
            $table->dropColumn(['company_id', 'mime_type']);
        });

        Schema::table('issue_report_replies', function (Blueprint $table) {
            $table->dropColumn(['company_id', 'user_id', 'author_name', 'is_internal']);
        });

        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone', 'resolution', 'closed_at', 'last_replied_at']);
        });
    }
};
