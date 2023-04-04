@props(['containerKey', 'maxDepth'])
<script src="https://code.jquery.com/jquery-3.6.1.slim.min.js"
    integrity="sha256-w8CvhFs7iHNVUtnSP0YKEg00p9Ih13rlL9zGqvLdePA="
    crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        let nestedTree = $('#{{ $containerKey }}').nestable({
            group: {{ $containerKey }},
            maxDepth: {{ $maxDepth }},
            expandBtnHTML: '',
            collapseBtnHTML: '',
        });

        // Custom expand/collapse buttons
        $('#{{ $containerKey }} .dd-item-btns [data-action="expand"]').on('click', function (el) {
            $(this).addClass('hidden');
            let list = $(this).closest('li');
            if (list.length) {
                $(this).addClass('hidden');
                list.find('.dd-item-btns [data-action="collapse"]').removeClass('hidden');
                list.find('.dd-list').removeClass('hidden');
            }
        });
        $('#{{ $containerKey }} .dd-item-btns [data-action="collapse"]').on('click', function (el) {
            let list = $(this).closest('li');
            if (list.length) {
                $(this).addClass('hidden');
                list.find('.dd-item-btns [data-action="expand"]').removeClass('hidden');
                list.find('.dd-list').addClass('hidden');
            }
        });

        $('#nestable-menu [data-action="expand-all"]').on('click', function () {
            $('.dd').nestable('expandAll');
        });
        $('#nestable-menu [data-action="collapse-all"]').on('click', function () {
            $('.dd').nestable('collapseAll');
        });
        $('#nestable-menu [data-action="save"]').on('click', async function (e) {
            let value = $('#{{ $containerKey }}').nestable('serialize');
            let result = await @this.updateTree(value);
            console.log(result);
            if (result['reload'] === true) {
                console.log('Reload Menu');
                window.location.reload();
            }
        });
    });
</script>