<?php

namespace App\Http\Controllers;

use App\Models\LibraryCard;
use App\Models\Reader;
use App\Services\IdGeneratorService;
use App\Services\StatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryCardController extends Controller
{
    public function __construct(
        IdGeneratorService $idGenerator,
        StatusService $statusService
    )
    {
        $this->idGenerator = $idGenerator;
        $this->statusService = $statusService;
    }

    public function index(Request $request)
    {
        $this->statusService->syncAllCardStatuses();

        $search = trim((string) $request->query('search', ''));

        $cards = LibraryCard::query()
            ->with('reader')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('maTTV', 'like', "%{$search}%")
                    ->orWhere('maDG', 'like', "%{$search}%")
                    ->orWhereHas('reader', function ($subQuery) use ($search) {
                        $subQuery->where('tenDG', 'like', "%{$search}%");
                    });
            })
            ->orderBy('maTTV')
            ->paginate(5)
            ->withQueryString();

        return view('library_cards.index', compact('cards', 'search'));
    }

    public function create()
    {
        $readers = Reader::orderBy('tenDG')->get();

        return view('library_cards.form', [
            'libraryCard' => new LibraryCard(),
            'readers' => $readers,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['maTTV'] = $this->idGenerator->make('thethuvien', 'maTTV', 'TTV');
        $data['trangThai'] = Carbon::parse($data['ngayHetHan'])->gte(Carbon::today()) ? 'Còn hạn' : 'Hết hạn';

        LibraryCard::create($data);

        return redirect()->route('thethuvien.index')->with('success', 'Thêm thẻ thư viện thành công.');
    }

    public function edit(LibraryCard $libraryCard)
    {
        $readers = Reader::orderBy('tenDG')->get();

        return view('library_cards.form', [
            'libraryCard' => $libraryCard,
            'readers' => $readers,
            'isEdit' => true,
        ]);
    }

    public function show(LibraryCard $libraryCard)
    {
        $libraryCard->load('reader');

        return view('library_cards.show', compact('libraryCard'));
    }

    public function update(Request $request, LibraryCard $libraryCard)
    {
        $data = $this->validateData($request, $libraryCard);
        $data['trangThai'] = Carbon::parse($data['ngayHetHan'])->gte(Carbon::today()) ? 'Còn hạn' : 'Hết hạn';

        $libraryCard->update($data);

        return redirect()->route('thethuvien.index')->with('success', 'Cập nhật thẻ thư viện thành công.');
    }

    public function destroy(LibraryCard $libraryCard)
    {
        $isActive = Carbon::parse($libraryCard->ngayHetHan)->gte(Carbon::today());
        if ($libraryCard->trangThai === 'Còn hạn' || $isActive) {
            return back()->with('danger', 'Không thể xóa thẻ thư viện còn hạn.');
        }

        $libraryCard->delete();

        return redirect()->route('thethuvien.index')->with('success', 'Xóa thẻ thư viện thành công.');
    }

    private function validateData(Request $request, ?LibraryCard $libraryCard = null): array
    {
        $id = optional($libraryCard)->maTTV;

        return $request->validate([
            'maDG' => [
                'required',
                Rule::exists('docgia', 'maDG'),
                Rule::unique('thethuvien', 'maDG')->ignore($id, 'maTTV'),
            ],
            'ngayCap' => ['required', 'date'],
            'ngayHetHan' => ['required', 'date', 'after_or_equal:ngayCap'],
        ], [
            //'maDG.required' => 'Vui lòng chọn độc giả.',
            'maDG.unique' => 'Độc giả này đã có thẻ thư viện.',
            // 'ngayCap.required' => 'Ngày cấp không được để trống.',
            // 'ngayHetHan.required' => 'Ngày hết hạn không được để trống.',
            'ngayHetHan.after_or_equal' => 'Ngày hết hạn phải lớn hơn hoặc bằng ngày cấp.',
        ]);
    }
}
