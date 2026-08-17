<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_endorsements', function (Blueprint $table) {
            if (! Schema::hasColumn('property_endorsements', 'property_id')) {
                $table->foreignId('property_id')->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('property_endorsements', 'event')) {
                $table->char('event', 1)->after('property_id');
            }

            if (! Schema::hasColumn('property_endorsements', 'measure')) {
                $table->decimal('measure', 12, 2)->nullable()->after('event');
            }

            if (! Schema::hasColumn('property_endorsements', 'description')) {
                $table->text('description')->after('measure');
            }

            if (! Schema::hasColumn('property_endorsements', 'occurred_on')) {
                $table->date('occurred_on')->after('description');
            }
        });

        if (! $this->indexExists('property_endorsements', 'property_endorsements_property_id_index')) {
            Schema::table('property_endorsements', function (Blueprint $table) {
                $table->index('property_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('property_endorsements', function (Blueprint $table) {
            if (Schema::hasColumn('property_endorsements', 'property_id')) {
                $table->dropConstrainedForeignId('property_id');
            }

            foreach (['event', 'measure', 'description', 'occurred_on'] as $column) {
                if (Schema::hasColumn('property_endorsements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->select(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index],
        );

        return (int) ($result[0]->aggregate ?? 0) > 0;
    }
};
