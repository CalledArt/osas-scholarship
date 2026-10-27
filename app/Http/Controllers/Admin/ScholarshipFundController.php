<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateScholarshipFundsRequest;
use App\Models\Scholarship;
use App\Support\Peso;
use Illuminate\Http\RedirectResponse;

class ScholarshipFundController extends Controller
{
    /**
     * Set the scholarship's total funds from its fund allocation, where they are split among the students who passed.
     */
    public function update(UpdateScholarshipFundsRequest $request, Scholarship $scholarship): RedirectResponse
    {
        $scholarship->update($request->validated());

        $passedCount = $scholarship->applications()->approved()->count();
        $share = $scholarship->fundShareInCentavos($passedCount);

        $message = $share === null
            ? __('Total funds saved. Each student\'s allocation will be shown once a pass list approves students.')
            : trans_choice('Total funds saved. The :count student who passed is allocated :share.|Total funds saved. Each of the :count students who passed is allocated :share.', $passedCount, [
                'count' => $passedCount,
                'share' => Peso::format($share),
            ]);

        return redirect()->route('admin.fund-allocation.show', $scholarship)->with('status', $message);
    }
}
