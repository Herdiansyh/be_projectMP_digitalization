<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employees', 'status')) {
            return;
        }

        // Drop index jika ada
        DB::statement("
            IF EXISTS (
                SELECT * FROM sys.indexes
                WHERE name = 'employees_status_index'
                AND object_id = OBJECT_ID('employees')
            )
            DROP INDEX employees_status_index ON employees;
        ");

        // Drop CHECK constraint jika ada
        DB::statement("
            DECLARE @sql NVARCHAR(MAX) = '';

            SELECT @sql += 'ALTER TABLE employees DROP CONSTRAINT [' + cc.name + '];'
            FROM sys.check_constraints cc
            WHERE cc.parent_object_id = OBJECT_ID('employees')
              AND cc.parent_column_id = (
                    SELECT column_id
                    FROM sys.columns
                    WHERE object_id = OBJECT_ID('employees')
                      AND name = 'status'
              );

            EXEC(@sql);
        ");

        // Drop DEFAULT constraint jika ada
        DB::statement("
            DECLARE @sql NVARCHAR(MAX) = '';

            SELECT @sql += 'ALTER TABLE employees DROP CONSTRAINT [' + dc.name + '];'
            FROM sys.default_constraints dc
            INNER JOIN sys.columns c
                ON c.default_object_id = dc.object_id
            WHERE c.object_id = OBJECT_ID('employees')
              AND c.name = 'status';

            EXEC(@sql);
        ");

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('status', [
                'active',
                'nonactive',
                'resigned'
            ])->default('active');

            $table->index('status');
        });
    }
};