<?php

use Illuminate\Support\Facades\Route;
use Seiger\sLMS\Http\AssessmentController;
use Seiger\sLMS\Http\LearnerAssessmentRequest;

Route::middleware(['web', LearnerAssessmentRequest::class])->prefix('slms')->name('slms.assessment.')->group(function (): void {
    Route::get('modules/{module}/test', [AssessmentController::class, 'entry'])->whereNumber('module')->name('entry');
    Route::post('modules/{module}/test', [AssessmentController::class, 'start'])->whereNumber('module')->name('start');
    Route::get('attempts/{attempt}', [AssessmentController::class, 'show'])->whereNumber('attempt')->name('attempt');
    Route::post('attempts/{attempt}/save', [AssessmentController::class, 'save'])->whereNumber('attempt')->name('save');
    Route::post('attempts/{attempt}/submit', [AssessmentController::class, 'submit'])->whereNumber('attempt')->name('submit');
});
