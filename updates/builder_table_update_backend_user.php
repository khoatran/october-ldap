<?php namespace CmgDev\CmgBackend\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBackendUser extends Migration
{
    public function up()
    {
        Schema::table('backend_users', function ($table) {
            $table->enum('khoatd_ldap_user_type', ['cms', 'ldap'])->default('cms');
        });

    }
    
    public function down()
    {
        Schema::table('backend_users', function ($table) {
            $table->dropColumn('khoatd_ldap_user_type');
        });
    }
}
