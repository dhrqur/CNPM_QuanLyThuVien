function confirmDelete(url){
    if(confirm("Bạn có chắc chắn muốn xóa không?")){
        window.location.href = url;
    }
}

function addBorrowRow(targetId, bookOptionsHtml){
    const wrap = document.getElementById(targetId);
    if(!wrap) return;

    const div = document.createElement("div");
    div.className = "borrow-book-row";
    div.innerHTML = `
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label class="form-label">Sách</label>
                <select name="maSach[]" class="form-select" required>
                    <option value="">-- Chọn sách --</option>
                    ${bookOptionsHtml}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Số lượng</label>
                <input type="number" min="1" name="soLuong[]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger w-100" onclick="removeBorrowRow(this, '${targetId}')">Xóa dòng</button>
            </div>
        </div>
    `;
    wrap.appendChild(div);
}

function removeBorrowRow(btn, targetId){
    const wrap = document.getElementById(targetId);
    if(!wrap) return;
    if(wrap.querySelectorAll(".borrow-book-row").length > 1){
        btn.closest(".borrow-book-row").remove();
    }
}