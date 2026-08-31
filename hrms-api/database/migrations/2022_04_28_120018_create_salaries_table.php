<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('nik', 36);
            $table->text('line')->nullable()->comment('Bisnis Unit');
            $table->text('gross')->nullable();
            $table->text('net_pay')->nullable();
            $table->text('work_day')->nullable();
            $table->text('pay_per_day')->nullable();
            $table->text('basic_salary')->nullable();
            $table->text('extra')->nullable();
            $table->text('extra_hour')->nullable();
            $table->text('third_shift')->nullable();
            $table->text('transport_meal')->nullable();
            $table->text('leave')->nullable();
            $table->text('job')->nullable();
            $table->text('skill')->nullable();
            $table->text('service')->nullable();
            $table->text('over_time')->nullable();
            $table->text('h1')->nullable();
            $table->text('other')->nullable();
            $table->text('big_day')->nullable();
            $table->text('complaint')->nullable();
            $table->text('long_shift')->nullable();
            $table->text('machine_standby')->nullable()->comment('Jaga Mesin');
            $table->text('half_day')->nullable()->comment('Setengah Hari');
            $table->text('salary_increases')->nullable();
            $table->text('go_on_holiday')->nullable()->comment('Masuk Hari Off');
            $table->text('work_days_allowance')->nullable()->comment('Intensif Hari Kerja');
            $table->text('pulse_allowance')->nullable()->comment('Tunjangan Pulsa');
            $table->text('premi_allowance')->nullable()->comment('Premi');
            $table->text('lanjut_allowance')->nullable()->comment('Lanjut');
            $table->text('services_allowance')->nullable()->comment('Tunjangan Masa Kerja');
            $table->text('total_other')->nullable();
            $table->text('employment_bpjs')->nullable()->comment('Bpjs Ketenagakerjaan');
            $table->text('kms')->nullable();
            $table->text('stamp')->nullable()->comment('Materai');
            $table->text('training_shirt')->nullable();
            $table->text('id_card')->nullable();
            $table->text('fine')->nullable()->comment('Denda');
            $table->text('health_bpjs')->nullable()->comment('Bpjs Kesehatan');
            $table->text('locker')->nullable();
            $table->text('handphone')->nullable()->comment('Potongan HP');
            $table->text('uniform')->nullable();
            $table->text('saving')->nullable()->comment('Tabungan');
            $table->text('off_day')->nullable()->comment('Potongan Tidak Masuk');
            $table->text('driving_license_deduction')->nullable()->comment('SIM');
            $table->text('short_deduction')->nullable()->comment('Ketekoran');
            $table->text('others_deduction')->nullable()->comment('Potongan Lain');
            $table->text('others_2_deduction')->nullable()->comment('Lain - lain');
            $table->text('total_deduction')->nullable()->comment('Total Potongan');
            $table->integer('month_period')->nullable();
            $table->integer('year_period')->nullable();
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
        Schema::dropIfExists('salaries');
    }
}
