// ES6 import
import 'jquery';
// CommonJS require
const $ = require('jquery');

import jQueryNestable from '../custom.nestable';

export default function treeNestableComponent({
    containerKey,
    maxDepth,
}) {
    return {
        containerKey,
        maxDepth,

        nestedTreeElement: null,
        nestedTree: null,

        init: function () {

            // Used for jQuery event
            let nestedTreeElement = $(this.containerKey);
            this.nestedTreeElement = nestedTreeElement;

            let nestedTree = this.compile(this.nestedTreeElement, {
                group: containerKey,
                maxDepth: maxDepth,
                expandBtnHTML: '',
                collapseBtnHTML: '',
            });
            this.nestedTree = nestedTree;
            // Old version for jQuery Nestable Plugin (for reference)
            // let nestedTree = this.nestedTreeElement.nestable({
            //     group: containerKey,
            //     maxDepth: maxDepth,
            //     expandBtnHTML: '',
            //     collapseBtnHTML: '',
            // });

            // Custom expand/collapse buttons
            this.nestedTreeElement.on('click', '.dd-item-btns [data-action=expand]', function (el) {
                let list = $(this).closest('li');
                if (list.length) {
                    $(this).addClass('hidden');
                    $(this).parent().children('.dd-item-btns [data-action=collapse]').removeClass('hidden');
                    list.find('> .dd-list').removeClass('hidden').show();
                    list.find('> .dd-list > .dd-item').removeClass('dd-collapsed hidden');
                }
            });
            this.nestedTreeElement.on('click', '.dd-item-btns [data-action=collapse]', function (el) {
                let list = $(this).closest('li');
                if (list.length) {
                    $(this).addClass('hidden');
                    $(this).parent().children('.dd-item-btns [data-action=expand]').removeClass('hidden');
                    list.find('> .dd-list').addClass('hidden').hide();
                    list.find('> .dd-list > .dd-item').addClass('dd-collapsed hidden');
                }
            });
        },

        /**
         * Compile the tree nestable
         * @param {*} element 
         * @param {*} params 
         * @returns 
         */
        compile: function (element, params) {
            return jQueryNestable.buildNestable(element, params);
        },

        /**
         * Save the tree
         */
        save: async function () {
            let value = jQueryNestable.buildNestable(this.nestedTree, 'serialize');
            // Save and reload the livewire
            let result = await this.$wire.updateTree(value);
            if (result['reload'] === true) {
                // Reset the data of the tree
                jQueryNestable.buildNestable(this.nestedTree, 'reset');
            }
        },

        /**
         * Collapse all the tree
         */
        collapseAll: function () {
            const dd = this.$refs.treeContainer;
            if (!dd) {
                return;
            }
            jQueryNestable.buildNestable($(dd), 'collapseAll'); // jQueryNestable.buildNestable($('.dd'), 'collapseAll');
            // $('.dd').nestable('collapseAll');
            $(dd).find('.dd-item-btns [data-action=expand]').removeClass('hidden'); // $('.dd').find('.dd-item-btns [data-action=expand]').removeClass('hidden');
            $(dd).find('.dd-item-btns [data-action=collapse]').addClass('hidden'); // $('.dd').find('.dd-item-btns [data-action=collapse]').addClass('hidden');
            $(dd).find('ol > li').find('li').addClass('hidden'); // $('.dd > ol > li').find('li').addClass('hidden');
        },

        /**
         * Expand all the tree
         */
        expandAll: function () {
            const dd = this.$refs.treeContainer;
            if (!dd) {
                return;
            }
            jQueryNestable.buildNestable($(dd), 'expandAll'); // jQueryNestable.buildNestable($('.dd'), 'expandAll');
            // $('.dd').nestable('expandAll');
            $(dd).find('.dd-item-btns [data-action=expand]').addClass('hidden'); // $('.dd').find('.dd-item-btns [data-action=expand]').addClass('hidden');
            $(dd).find('.dd-item-btns [data-action=collapse]').removeClass('hidden'); // $('.dd').find('.dd-item-btns [data-action=collapse]').removeClass('hidden');
            $(dd).find('ol > li').find('li').removeClass('hidden'); // $('.dd > ol > li').find('li').removeClass('hidden');
        },
    }
}