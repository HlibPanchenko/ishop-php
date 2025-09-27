$(function () {

    $('.delete').click(function () {
        let res = confirm('Подтвердите действие');
        if (!res) return false;
    });

    $(".is-download").select2({
        placeholder: "Начните вводить наименование файла",
        minimumInputLength: 1,
        cache: true,
        ajax: {
            url: ADMIN + "/product/get-download",
            delay: 250,
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function (data, params) {
                return {
                    results: data.items,
                };
            },
        },
    });
    // чтобы автофокус был сразу на поле
    $('#is_download').on('select2:open', function () {
        document.querySelector('.select2-search__field').focus();
    });

    // когда уже выбрали файл, появляется кнопка "обычный товар" чтобы вернуться обратно с цифрового на обычный
    $('#is_download').on('select2:select', function () {
        $('.clear-download').remove();
        $('#is_download').before('<p class="clear-download"><span class="btn btn-danger">Обычный товар</span></p>');
    });

    // удаялем тот файл, что выбрали
    $('body').on('click', '.clear-download span', function () {
        $('#is_download').val(null).trigger('change');
        $('.clear-download').remove();
    });
    // удаление картинки
    $('.card-body').on('click', '.del-img', function () {
        const parentDiv = $(this).closest('.product-img-upload').remove();
        return false;
    });

    bsCustomFileInput.init();


});