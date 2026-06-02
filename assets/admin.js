(function($){
    let services = blbAdmin.services || [];

    function uid() { return 'svc_' + Math.random().toString(36).slice(2,8); }

    function renderTable() {
        const $body = $('#blb-svc-body');
        $body.empty();
        services.forEach(function(s, i) {
            $body.append(`
                <tr data-idx="${i}">
                    <td><span class="blb-drag-handle dashicons dashicons-menu" title="Drag untuk ubah urutan"></span></td>
                    <td><input type="text" class="blb-inp-name" value="${escHtml(s.name)}" placeholder="Nama layanan"></td>
                    <td><input type="text" class="blb-inp-sub"  value="${escHtml(s.sub)}"  placeholder="Sub-judul"></td>
                    <td><input type="text" class="blb-inp-price" value="${escHtml(s.price)}" placeholder="IDR 25k/kg"></td>
                    <td>
                        <select class="blb-inp-unit">
                            ${['kg','item','pair','set','shirt'].map(u=>`<option value="${u}"${s.unit===u?' selected':''}>${u}</option>`).join('')}
                        </select>
                    </td>
                    <td style="text-align:center;">
                        <input type="checkbox" class="blb-inp-active" ${s.active?'checked':''}>
                    </td>
                    <td>
                        <button class="blb-del-btn" data-idx="${i}">Hapus</button>
                    </td>
                </tr>`);
        });
        initDragSort();
    }

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function collectFromTable() {
        const rows = [];
        $('#blb-svc-body tr').each(function() {
            const idx = $(this).data('idx');
            const orig = services[idx] || {};
            rows.push({
                id:     orig.id || uid(),
                name:   $(this).find('.blb-inp-name').val().trim(),
                sub:    $(this).find('.blb-inp-sub').val().trim(),
                price:  $(this).find('.blb-inp-price').val().trim(),
                unit:   $(this).find('.blb-inp-unit').val(),
                active: $(this).find('.blb-inp-active').is(':checked'),
            });
        });
        return rows;
    }

    function initDragSort() {
        const tbody = document.getElementById('blb-svc-body');
        let dragging = null;
        tbody.querySelectorAll('.blb-drag-handle').forEach(function(handle) {
            handle.addEventListener('mousedown', function(e) {
                const tr = handle.closest('tr');
                dragging = tr;
                tr.classList.add('blb-dragging');
            });
        });
        tbody.addEventListener('dragover', function(e) { e.preventDefault(); });
        document.addEventListener('mouseup', function() {
            if (dragging) dragging.classList.remove('blb-dragging');
            dragging = null;
        });
        tbody.querySelectorAll('tr').forEach(function(tr) {
            tr.setAttribute('draggable', true);
            tr.addEventListener('dragstart', function() { dragging = tr; tr.classList.add('blb-dragging'); });
            tr.addEventListener('dragend', function() { tr.classList.remove('blb-dragging'); });
            tr.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (dragging && dragging !== tr) {
                    const rect = tr.getBoundingClientRect();
                    const mid = rect.top + rect.height / 2;
                    if (e.clientY < mid) tbody.insertBefore(dragging, tr);
                    else tbody.insertBefore(dragging, tr.nextSibling);
                }
            });
        });
    }

    $('#blb-add-svc').on('click', function() {
        services = collectFromTable();
        services.push({ id: uid(), name: '', sub: '', price: '', unit: 'kg', active: true });
        renderTable();
        $('#blb-svc-body tr:last-child .blb-inp-name').focus();
    });

    $(document).on('click', '.blb-del-btn', function() {
        if (!confirm('Hapus layanan ini?')) return;
        const idx = $(this).data('idx');
        services.splice(idx, 1);
        renderTable();
    });

    $('#blb-save-svc').on('click', function() {
        services = collectFromTable();
        const $status = $('#blb-save-status');
        $status.show();
        $.post(blbAdmin.ajaxurl, {
            action:   'blb_save_services',
            nonce:    blbAdmin.nonce,
            services: services,
        }, function(res) {
            $status.hide();
            const $notice = $('#blb-notice');
            if (res.success) {
                $notice.find('p').text('Layanan berhasil disimpan (' + res.data.count + ' layanan).');
                $notice.removeClass('notice-error').addClass('notice-success').show();
                renderTable();
            } else {
                $notice.find('p').text('Gagal menyimpan. Coba lagi.');
                $notice.removeClass('notice-success').addClass('notice-error').show();
            }
            setTimeout(() => $notice.fadeOut(), 4000);
        });
    });

    renderTable();

})(jQuery);
