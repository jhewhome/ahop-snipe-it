<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Patient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request): View
    {
        $this->authorize('index', Patient::class);

        $query = Patient::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('bhc_id', 'like', '%'.$search.'%')
                    ->orWhere('contact_number', 'like', '%'.$search.'%');
            });
        }

        $patients = $query->paginate(25)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        $this->authorize('create', Patient::class);

        $item = new Patient;
        $item->bhc_id = Patient::generateNextBhcId();

        return view('patients.edit', compact('item'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Patient::class);

        $patient = new Patient;
        $patient->fill($request->only([
            'bhc_id',
            'full_name',
            'sex',
            'birthdate',
            'contact_number',
            'notes',
        ]));
        $patient->created_by = auth()->id();
        $patient->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($patient->save()) {
            return redirect()->route('patients.index')->with('success', trans('admin/patients/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($patient->getErrors());
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        return view('patients.view', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        return view('patients.edit', ['item' => $patient]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);

        $patient->fill($request->only([
            'bhc_id',
            'full_name',
            'sex',
            'birthdate',
            'contact_number',
            'notes',
        ]));
        $patient->company_id = Company::getIdForCurrentUser($request->input('company_id'));

        if ($patient->save()) {
            return redirect()->route('patients.show', $patient)->with('success', trans('admin/patients/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($patient->getErrors());
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->authorize('delete', $patient);

        if ($patient->delete()) {
            return redirect()->route('patients.index')->with('success', trans('admin/patients/message.delete.success'));
        }

        return redirect()->back()->with('error', trans('admin/patients/message.delete.error'));
    }
}
