/**
 * Form Engine — Client-Side Logic
 *
 * Handles tab navigation, conditional logic, WP Media Library integration,
 * WP Color Picker, repeater rows, client-side validation, and save (REST + AJAX).
 *
 * Receives window.dbpFormData (localized by Form_Module).
 *
 * @package DirectoriesBuilderPro
 */
(function( $, window, document ) {
    'use strict';

    var formData = window.dbpFormData || {};

    /* ================================================================
     * 1. TAB NAVIGATION
     * ================================================================ */
    function initTabs() {
        $( document ).on( 'click', '.dbp-form__tab', function() {
            var $tab  = $( this );
            var $form = $tab.closest( '.dbp-form' );
            var tab   = $tab.data( 'tab' );

            // Update active tab.
            $form.find( '.dbp-form__tab' ).removeClass( 'dbp-form__tab--active' );
            $tab.addClass( 'dbp-form__tab--active' );

            // Show/hide groups.
            $form.find( '.dbp-form__group' ).each( function() {
                var $group = $( this );
                if ( $group.data( 'tab' ) === tab ) {
                    $group.show();
                } else {
                    $group.hide();
                }
            });
        });
    }

    /* ================================================================
     * 2. CONDITIONAL LOGIC
     * ================================================================ */
    function initConditionalLogic() {
        $( '.dbp-field[data-condition]' ).each( function() {
            var $field    = $( this );
            var condition = parseCondition( $field.attr( 'data-condition' ) );
            if ( ! condition ) return;

            var $form   = $field.closest( '.dbp-form' );
            var $source = findSourceField( $form, condition.field );

            if ( $source.length === 0 ) return;

            // Attach change listener to source field.
            $source.on( 'change input', function() {
                evaluateAndToggle( $field, $source, condition );
            });

            // Initial evaluation.
            evaluateAndToggle( $field, $source, condition );
        });
    }

    function parseCondition( raw ) {
        if ( ! raw ) return null;
        try {
            return JSON.parse( raw );
        } catch( e ) {
            return null;
        }
    }

    function findSourceField( $form, fieldId ) {
        // Try checkbox/toggle (hidden + checkbox pair).
        var $cb = $form.find( '#dbp_' + fieldId );
        if ( $cb.length ) return $cb;

        // Try radio group.
        var $radios = $form.find( 'input[name="dbp_fields[' + fieldId + ']"]' );
        if ( $radios.length ) return $radios;

        return $();
    }

    function getSourceValue( $source ) {
        if ( $source.length === 0 ) return null;

        // Radio group.
        if ( $source.first().is( ':radio' ) ) {
            return $source.filter( ':checked' ).val() || '';
        }
        // Checkbox / toggle.
        if ( $source.is( ':checkbox' ) ) {
            return $source.is( ':checked' );
        }
        // Select, text, etc.
        return $source.val();
    }

    function evaluateCondition( sourceValue, condition ) {
        var expected = condition.value;
        var operator = condition.operator || '=';

        // Normalize booleans.
        if ( expected === true || expected === 'true' || expected === '1' ) {
            expected = true;
        }
        if ( expected === false || expected === 'false' || expected === '0' || expected === '' ) {
            expected = false;
        }
        if ( sourceValue === true || sourceValue === 'true' || sourceValue === '1' ) {
            sourceValue = true;
        }
        if ( sourceValue === false || sourceValue === 'false' || sourceValue === '0' || sourceValue === '' ) {
            sourceValue = false;
        }

        switch ( operator ) {
            case '!=':
                return sourceValue !== expected;
            case 'in':
                if ( Array.isArray( expected ) ) {
                    return expected.indexOf( sourceValue ) !== -1;
                }
                return sourceValue === expected;
            default: // '='
                return sourceValue === expected;
        }
    }

    function evaluateAndToggle( $field, $source, condition ) {
        var sourceValue = getSourceValue( $source );
        var show = evaluateCondition( sourceValue, condition );
        if ( show ) {
            $field.addClass( 'dbp-field--visible' ).show();
        } else {
            $field.removeClass( 'dbp-field--visible' ).hide();
        }
    }

    /* ================================================================
     * 3. WP MEDIA LIBRARY INTEGRATION
     * ================================================================ */
    function initMediaFields() {
        $( document ).on( 'click', '.dbp-media-select', function( e ) {
            e.preventDefault();
            var $btn     = $( this );
            var $wrapper = $btn.closest( '.dbp-field, td' );
            var $input   = $wrapper.find( '.dbp-media-input' );
            var $preview = $wrapper.find( '.dbp-media-preview' );
            var $img     = $preview.find( '.dbp-media-preview__img' );
            var $remove  = $wrapper.find( '.dbp-media-remove' );

            var frame = wp.media({
                title: formData.i18n ? formData.i18n.selectImage || 'Select Image' : 'Select Image',
                button: { text: formData.i18n ? formData.i18n.useImage || 'Use Image' : 'Use Image' },
                multiple: false,
                library: { type: 'image' }
            });

            frame.on( 'select', function() {
                var attachment = frame.state().get( 'selection' ).first().toJSON();
                var thumbUrl   = attachment.sizes && attachment.sizes.thumbnail
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                $input.val( attachment.id ).trigger( 'change' );
                $img.attr( 'src', thumbUrl );
                $preview.show();
                $remove.show();
            });

            frame.open();
        });

        $( document ).on( 'click', '.dbp-media-remove', function( e ) {
            e.preventDefault();
            var $btn     = $( this );
            var $wrapper = $btn.closest( '.dbp-field, td' );
            $wrapper.find( '.dbp-media-input' ).val( '0' ).trigger( 'change' );
            $wrapper.find( '.dbp-media-preview' ).hide();
            $btn.hide();
        });
    }

    /* ================================================================
     * 4. WP COLOR PICKER
     * ================================================================ */
    function initColorPickers() {
        if ( $.fn.wpColorPicker ) {
            $( '.dbp-field--color input[data-colorpicker]' ).wpColorPicker();
        }
    }

    /* ================================================================
     * 5. REPEATER ROWS
     * ================================================================ */
    function initRepeaters() {
        // Add row.
        $( document ).on( 'click', '.dbp-repeater__add', function() {
            var $repeater = $( this ).closest( '.dbp-repeater' );
            var $body     = $repeater.find( '.dbp-repeater__body' );
            var $rows     = $body.find( '.dbp-repeater__row' );
            var $template = $rows.first();
            var $newRow   = $template.clone( true );
            var newIndex  = $rows.length;

            // Clear input values in new row.
            $newRow.find( 'input[type="text"], input[type="number"], input[type="email"], input[type="url"], textarea' ).val( '' );
            $newRow.find( 'input[type="hidden"]' ).each( function() {
                if ( $( this ).hasClass( 'dbp-media-input' ) ) {
                    $( this ).val( '0' );
                }
            });
            $newRow.find( 'input[type="checkbox"]' ).prop( 'checked', false );
            $newRow.find( 'select' ).prop( 'selectedIndex', 0 );
            $newRow.find( '.dbp-media-preview' ).hide();
            $newRow.find( '.dbp-media-remove' ).hide();

            // Update data-index.
            $newRow.attr( 'data-index', newIndex );

            // Re-index names.
            reindexRow( $newRow, $repeater.data( 'field-id' ), newIndex );

            $body.append( $newRow );
            updateRemoveButtons( $repeater );

            // Init any color pickers in new row.
            if ( $.fn.wpColorPicker ) {
                $newRow.find( 'input[data-colorpicker]' ).wpColorPicker();
            }
        });

        // Remove row.
        $( document ).on( 'click', '.dbp-repeater__remove', function() {
            var $repeater = $( this ).closest( '.dbp-repeater' );
            var $body     = $repeater.find( '.dbp-repeater__body' );
            var $rows     = $body.find( '.dbp-repeater__row' );

            if ( $rows.length <= 1 ) return; // Minimum 1 row enforced.

            $( this ).closest( '.dbp-repeater__row' ).remove();
            reindexAllRows( $repeater );
            updateRemoveButtons( $repeater );
        });

        // Initial state: hide remove if only 1 row.
        $( '.dbp-repeater' ).each( function() {
            updateRemoveButtons( $( this ) );
        });
    }

    function reindexRow( $row, fieldId, index ) {
        $row.find( 'input, select, textarea' ).each( function() {
            var $el  = $( this );
            var name = $el.attr( 'name' );
            if ( ! name ) return;
            // Replace [fieldId][N] with [fieldId][newIndex].
            var regex = new RegExp( '\\[' + escapeRegex( fieldId ) + '\\]\\[\\d+\\]' );
            $el.attr( 'name', name.replace( regex, '[' + fieldId + '][' + index + ']' ) );

            // Update IDs too.
            var id = $el.attr( 'id' );
            if ( id ) {
                var idRegex = new RegExp( fieldId + '_\\d+_' );
                $el.attr( 'id', id.replace( idRegex, fieldId + '_' + index + '_' ) );
            }
        });
    }

    function reindexAllRows( $repeater ) {
        var fieldId = $repeater.data( 'field-id' );
        $repeater.find( '.dbp-repeater__row' ).each( function( idx ) {
            $( this ).attr( 'data-index', idx );
            reindexRow( $( this ), fieldId, idx );
        });
    }

    function updateRemoveButtons( $repeater ) {
        var $rows = $repeater.find( '.dbp-repeater__row' );
        var $btns = $repeater.find( '.dbp-repeater__remove' );
        if ( $rows.length <= 1 ) {
            $btns.hide();
        } else {
            $btns.show();
        }
    }

    function escapeRegex( str ) {
        return str.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
    }

    /* ================================================================
     * 6. CLIENT-SIDE VALIDATION
     * ================================================================ */
    function validateForm( $form ) {
        var isValid = true;

        // Clear previous errors.
        $form.find( '.dbp-field--error' ).removeClass( 'dbp-field--error' );
        $form.find( '.dbp-field__error' ).text( '' );

        // Only validate visible fields.
        $form.find( '.dbp-field[data-required="true"]:visible' ).each( function() {
            var $field = $( this );
            var $input = $field.find( 'input:not([type="hidden"]), select, textarea' ).first();
            var val    = '';

            if ( $input.is( ':checkbox' ) ) {
                return; // Checkboxes/toggles can be unchecked.
            }

            val = $input.val();
            if ( ! val || $.trim( val ) === '' ) {
                markFieldError( $field, formData.i18n ? formData.i18n.fieldRequired || 'This field is required.' : 'This field is required.' );
                isValid = false;
            }
        });

        // Email validation.
        $form.find( '.dbp-field--email:visible input' ).each( function() {
            var val = $.trim( $( this ).val() );
            if ( val && ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( val ) ) {
                markFieldError( $( this ).closest( '.dbp-field' ), formData.i18n ? formData.i18n.invalidEmail || 'Invalid email address.' : 'Invalid email address.' );
                isValid = false;
            }
        });

        // Number min/max validation.
        $form.find( '.dbp-field--number:visible' ).each( function() {
            var $field = $( this );
            var $input = $field.find( 'input[type="number"]' );
            var val    = parseFloat( $input.val() );
            if ( isNaN( val ) ) return;

            var min = $field.data( 'min' );
            var max = $field.data( 'max' );

            if ( min !== undefined && val < parseFloat( min ) ) {
                markFieldError( $field, 'Minimum value is ' + min + '.' );
                isValid = false;
            }
            if ( max !== undefined && val > parseFloat( max ) ) {
                markFieldError( $field, 'Maximum value is ' + max + '.' );
                isValid = false;
            }
        });

        return isValid;
    }

    function markFieldError( $field, message ) {
        $field.addClass( 'dbp-field--error' );
        $field.find( '.dbp-field__error' ).first().text( message );
    }

    /* ================================================================
     * 7. SAVE (REST preferred, AJAX fallback)
     * ================================================================ */
    function initSave() {
        $( document ).on( 'click', '.dbp-form__save', function( e ) {
            e.preventDefault();
            var $btn  = $( this );
            var $form = $btn.closest( '.dbp-form' );
            var name  = $form.data( 'form-name' );
            var objId = $form.data( 'object-id' ) || null;

            // Validate first.
            if ( ! validateForm( $form ) ) {
                showStatus( $form, 'error', formData.i18n ? formData.i18n.validationFailed || 'Please fix the errors above.' : 'Please fix the errors above.' );
                return;
            }

            // Collect values.
            var values = collectValues( $form );

            // Loading state.
            $btn.addClass( 'is-loading' ).prop( 'disabled', true );
            clearStatus( $form );

            // Try REST first.
            saveViaREST( name, values, objId, $form, $btn );
        });
    }

    function collectValues( $form ) {
        var values = {};
        var formArray = $form.find( 'input, select, textarea' ).serializeArray();

        for ( var i = 0; i < formArray.length; i++ ) {
            var item = formArray[i];
            var name = item.name;
            var val  = item.value;

            // Only process dbp_fields[...] names.
            if ( name.indexOf( 'dbp_fields[' ) !== 0 ) continue;

            // Parse the field path: dbp_fields[id] or dbp_fields[id][0][sub_id].
            var path = parseFieldName( name );
            if ( path.length === 0 ) continue;

            setNestedValue( values, path, val );
        }

        // Handle unchecked checkboxes (hidden input with value "0").
        $form.find( 'input[type="checkbox"]' ).each( function() {
            var $cb = $( this );
            if ( $cb.hasClass( 'dbp-toggle__input' ) || $cb.attr( 'name' ) ) {
                var name = $cb.attr( 'name' );
                if ( ! name || name.indexOf( 'dbp_fields[' ) !== 0 ) return;
                var path = parseFieldName( name );
                if ( path.length === 0 ) return;
                setNestedValue( values, path, $cb.is( ':checked' ) ? '1' : '0' );
            }
        });

        return values;
    }

    function parseFieldName( name ) {
        // dbp_fields[some_id] → ['some_id']
        // dbp_fields[hours][0][day] → ['hours', '0', 'day']
        var matches = name.match( /dbp_fields\[([^\]]+)\](?:\[([^\]]*)\])*/ );
        if ( ! matches ) return [];

        var parts = [];
        // Get all bracket contents.
        var regex = /\[([^\]]*)\]/g;
        var m;
        var stripped = name.replace( 'dbp_fields', '' );
        while ( ( m = regex.exec( stripped ) ) !== null ) {
            parts.push( m[1] );
        }
        return parts;
    }

    function setNestedValue( obj, path, value ) {
        var current = obj;
        for ( var i = 0; i < path.length - 1; i++ ) {
            var key = path[i];
            var nextKey = path[i + 1];
            if ( current[key] === undefined ) {
                // If next key is numeric, create array; otherwise object.
                current[key] = /^\d+$/.test( nextKey ) ? [] : {};
            }
            current = current[key];
        }
        current[ path[ path.length - 1 ] ] = value;
    }

    function saveViaREST( formName, values, objectId, $form, $btn ) {
        var url  = formData.restBase + 'forms/' + formName + '/save';
        var body = { values: values };
        if ( objectId ) {
            body.object_id = parseInt( objectId, 10 );
        }

        $.ajax({
            url: url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify( body ),
            beforeSend: function( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', formData.restNonce || '' );
            },
            success: function( response ) {
                $btn.removeClass( 'is-loading' ).prop( 'disabled', false );
                showStatus( $form, 'success', response.message || 'Saved.' );
                autoDismissStatus( $form, 3000 );
            },
            error: function( xhr ) {
                $btn.removeClass( 'is-loading' ).prop( 'disabled', false );
                // On 403, fall back to AJAX.
                if ( xhr.status === 403 ) {
                    saveViaAJAX( formName, values, objectId, $form, $btn );
                    return;
                }
                var msg = 'An error occurred.';
                if ( xhr.responseJSON ) {
                    if ( xhr.responseJSON.message ) {
                        msg = Array.isArray( xhr.responseJSON.message )
                            ? xhr.responseJSON.message.join( '\n' )
                            : xhr.responseJSON.message;
                    }
                }
                showStatus( $form, 'error', msg );
            }
        });
    }

    function saveViaAJAX( formName, values, objectId, $form, $btn ) {
        $btn.addClass( 'is-loading' ).prop( 'disabled', true );

        var data = {
            action:    'dbp_save_form',
            nonce:     formData.nonce || '',
            form_name: formName,
            values:    values
        };
        if ( objectId ) {
            data.object_id = parseInt( objectId, 10 );
        }

        $.post( formData.ajaxurl, data, function( response ) {
            $btn.removeClass( 'is-loading' ).prop( 'disabled', false );
            if ( response.success ) {
                showStatus( $form, 'success', response.data.message || 'Saved.' );
                autoDismissStatus( $form, 3000 );
            } else {
                var msg = 'An error occurred.';
                if ( response.data && response.data.message ) {
                    msg = Array.isArray( response.data.message )
                        ? response.data.message.join( '\n' )
                        : response.data.message;
                }
                showStatus( $form, 'error', msg );
            }
        }).fail( function() {
            $btn.removeClass( 'is-loading' ).prop( 'disabled', false );
            showStatus( $form, 'error', 'Network error. Please try again.' );
        });
    }

    function showStatus( $form, type, message ) {
        var $status = $form.find( '.dbp-form__status' );
        $status
            .removeClass( 'dbp-form__status--success dbp-form__status--error' )
            .addClass( 'dbp-form__status--' + type )
            .text( message );
    }

    function clearStatus( $form ) {
        $form.find( '.dbp-form__status' ).removeClass( 'dbp-form__status--success dbp-form__status--error' ).text( '' );
    }

    function autoDismissStatus( $form, delay ) {
        setTimeout( function() {
            clearStatus( $form );
        }, delay );
    }

    /* ================================================================
     * 8. AUTO-SAVE (behind flag)
     * ================================================================ */
    function initAutoSave() {
        if ( ! formData.autosave ) return;

        var debounceTimer;
        $( document ).on( 'change input', '.dbp-form input, .dbp-form select, .dbp-form textarea', function() {
            var $form = $( this ).closest( '.dbp-form' );
            clearTimeout( debounceTimer );
            debounceTimer = setTimeout( function() {
                var name   = $form.data( 'form-name' );
                var objId  = $form.data( 'object-id' ) || null;
                var values = collectValues( $form );
                var $btn   = $form.find( '.dbp-form__save' );

                // Silent save — no success toast, only error.
                var url  = formData.restBase + 'forms/' + name + '/save';
                var body = { values: values };
                if ( objId ) body.object_id = parseInt( objId, 10 );

                $.ajax({
                    url: url,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify( body ),
                    beforeSend: function( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', formData.restNonce || '' );
                    },
                    error: function( xhr ) {
                        if ( xhr.responseJSON && xhr.responseJSON.message ) {
                            showStatus( $form, 'error', xhr.responseJSON.message );
                        }
                    }
                });
            }, 2000 );
        });
    }

    /* ================================================================
     * INIT
     * ================================================================ */
    $( document ).ready( function() {
        initTabs();
        initConditionalLogic();
        initMediaFields();
        initColorPickers();
        initRepeaters();
        initSave();
        initAutoSave();
    });

})( jQuery, window, document );
