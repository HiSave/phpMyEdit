<?php

/*
 * phpMyEdit - instant MySQL table editor and code generator
 *
 * extensions/phpMyEdit-mce-cal.class.php - phpMyEdit html area & calendar popup extension
 * ____________________________________________________________
 *
 * Contribution of Adam Hammond <php@pixolet.co.uk>, London, UK
 * Contribution of Ezudin Kurtowich <ekurtovic@ieee.org>, Sarajevo
 * Copyright (c) 2003-2008 Platon Group, http://platon.sk/
 * All rights reserved.
 *
 * Updated 18th Mar 2008
 *
 * Updated to be in sync with latest CVS
 *
 *
 * Updated 28th Jul 2005
 *
 * Updated to use TinyMCE instead of HTMLArea
 * Updated to handle multiple tabs and to use PME prefixes.
 * Updated to include sub-form patch
 *
 *
 *
 * See README file for more information about this software.
 * See COPYING file for license information.
 *
 * Download the latest version from
 * http://platon.sk/projects/phpMyEdit/
 */

/* $Platon: phpMyEdit/extensions/phpMyEdit-mce-cal.class.php,v 1.9 2008-05-21 17:06:52 ajh Exp $ */

/*
    OVERVIEW
    --------

    mce_cal extends the standard phpMyEdit class to allow
    a calendar popup helper to be put on any text field and for any textarea
    field to turned into an HTML editor.
    This extension uses the free jsCalendar from http://www.dynarch.com/projects/calendar/
    and the TinyMCE code from http://tinymce.moxiecode.com/

    REQUIREMENTS
    ------------

    The requirement is a properly installed jsCalendar and TinyMCE script.
    All browsers supported by these scripts are supported by this
    extension. Note that version 1.44 or later for TinyMCE is required.

    USAGE
    -----

    For both features:

    1. Call to phpMyEdit-mce-cal.class.php instead
       of phpMyEdit.class.php.

       Example:

       require_once 'extensions/phpMyEdit-mce-cal.class.php';
       new phpMyEdit_mce_cal($opts);



    HTML TextArea

    This enables WYSIWYG editing of a textarea field.
    In order to use it, you should:

    1.     Load TinyMCE script in the <head>...</head> section of your
           phpMyEdit calling program as described in the htmlarea manual.

           <!-- tinyMCE -->
        <script language="javascript" type="text/javascript" src="js/<path to TinyMCE>"></script>
        <script language="javascript" type="text/javascript">
           tinyMCE.init({
              editor_selector : "mceEditor",
              auto_reset_designmode : true
           });
        </script>
        <!-- /tinyMCE -->

          where 'js/<path to TinyMCE>' is the path to the javascript code

        NOTES:
        A.  Previous versions of this extension used "specific_textareas" mode - this
            has now been deprecated in favour of "editor_selector". "editor_selector" must
            always be set, by default it should be set to "mceEditor".

        B.    Due to a bug in Mozilla, if any of the textareas being used as HTML
            editors are in tabs and are initially hidden, the width and height
            need to be specified in the tinyMCE initialization and
            'auto_reset_designmode' must be set to 'true':

            tinyMCE.init({
                  editor_selector : "mceEditor",
                  auto_reset_designmode : true,
                  width: "800",
                  height: "200"
               });

    2.     Add 'html'=>true parameter to the textarea field definition
           in your phpMyEdit calling program.

           Example:

           $opts['fdd']['col_name'] = array(
             'name'     => 'Column',
             'select'   => 'T',
             'options'  => 'ACPVD',
             'required' => true,
             'textarea' => array(
                   'html' => true,
                   'rows' => 11,
                   'cols' => 81)
           );

    3.  It is also possible to have multiple text area formats on the same
        form. This is done by specifying a text tag for the textarea:

           $opts['fdd']['col_name'] = array(
             'name'     => 'Column',
             'select'   => 'T',
             'options'  => 'ACPVD',
             'required' => true,
             'textarea' => array(
                   'html' => 'format1',
                   'rows' => 11,
                   'cols' => 81)
           );

        You then need to initialize TinyMCE in the header to recognize all of
        the tags used in the textareas.

        EXAMPLE
        In the following, two formats of tinyMCE editor are defined.

        This example is the default, and will be used for any fields where
        'html' is set to true.

                tinyMCE.init({
                       editor_selector : "mceEditor",
                       auto_reset_designmode : true
                });

        This second example uses a different "editor_selector". Instead of setting the
        'html' option to true, you should set it to your chosed "editor_selector"
        set to the text tag given to the textarea in PME with 'mce_' prepended
        to it.

            tinyMCE.init({
                   editor_selector : "mceFormat1",
                   auto_reset_designmode : true,
                theme : "advanced",
                   width: "800",
                   height: "200",
                   plugins : "table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,zoom,flash,searchreplace,print",
                theme_advanced_buttons1_add_before : "save,separator",
                theme_advanced_buttons1_add : "fontselect,fontsizeselect",
                theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor",
                theme_advanced_buttons2_add_before: "cut,copy,paste,separator,search,replace,separator",
                theme_advanced_buttons3_add_before : "tablecontrols,separator",
                theme_advanced_buttons3_add : "emotions,iespell,flash,advhr,separator,print",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_path_location : "bottom",
                content_css : "example_full.css",
                plugin_insertdate_dateFormat : "%Y-%m-%d",
                plugin_insertdate_timeFormat : "%H:%M:%S",
                extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"

        });

        So:
            'html' => true             maps to     editor_selector : "mceEditor"
            'html' => 'mceFormat1'     maps to     editor_selector : "mceFormat1"
            'html' => 'foo'            maps to     editor_selector : "foo"
            'html' => 'bar'            maps to     editor_selector : "bar"

        You can initialize TinyMCE as many times as you need to give you as many
        editor formats as you need.

    CALENDAR

    This extension enables the display of a popup calendar selection
    against selected fields.

    In order to use it, you should:

    1. Load the jsCalendar scripts in the <head>...</head> section of
       your phpMyEdit calling program, substituting the correct paths:

       <script type="text/javascript" src="js/jscalendar/calendar.js"></script>
       <script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
       <script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

    2. Choose your preferred jsCalendar CSS file (see jsCalendar
       documentation) and add the following in the <head>...</head>
       section of your phpMyEdit calling program, substituting the
       correct path:

        <link rel="stylesheet" type="text/css" media="screen"
                href="js/jscalendar/calendar-system.css">

    3. Add 'calendar' parameter to the field definitions where you
       want a calendar popup in your phpMyEdit calling program.

       Example:

       $opts['fdd']['col_name'] = array(
         'name'     => 'Column',
         'select'   => 'T',
         'options'  => 'ACPVD',
         'required' => true,
         'calendar' => true
       );

       This is will display a button next to the field which pops up
       a calendar when clicked. If that field has a 'strftimemask'
       parameter set, it will use this for the date format.

       For more advanced usage, you can set the 'calendar' parameter
       to an array of valid jsCalendar Calendar.setup options
       (see jSCalendar document for details). Note that not all
       of these options make sense to use in phpMyEdit, and some
       of them will actively break the function.

       Example:

       $opts['fdd']['col_name'] = array(
         'name'     => 'Column',
         'select'   => 'T',
         'options'  => 'ACPVD',
         'required' => true,
         'calendar' => array(
           'ifFormat'    => '%Y/%m/%d', // defaults to the ['strftimemask']
           'firstDay'    => 1,          // 0 = Sunday, 1 = Monday
           'singleClick' => true,       // single or double click to close
           'weekNumbers' => true,       // Show week numbers
           'showsTime'   => false,      // Show time as well as date
           'timeFormat'  => '24',       // 12 or 24 hour clock
           'button'      => true,       // Display button (rather then clickable area)
           'label'       => '...',      // button label (used by phpMyEdit)
           'date'        => '2003-12-19 10:00' // Initial date/time for popup
                                               // (see notes below)
         )
       );

    NOTES
    -----

    1. The popup will normally set the initial value to the current
       field value or to current date/time. 'date' option will always
       override this, even if there is a current date/time value
       in the field. If you want a default value only if the field
       is currently empty, use the phpMyEdit 'default' option.

    2. Only the options listed above may be set by the user, any other
       options will be ignored.

    3. The 'label' option can contain HTML markup which will be displayed as
       the button/clickable area to pull up the calendar

    SEARCH KEYWORD
    --------------

    Search for "mce_cal" string in this source code,
    to find all extension related modifications.
*/

require_once dirname(__FILE__).'/../phpMyEdit.class.php';

class phpMyEdit_mce_cal extends phpMyEdit
{
    var $calendars; // Array for collecting list of fields with calendar popups

    /* Array of valid options for passing to Calendar.setup */
    var $valid_opts = array(
            'button','ifFormat','singleClick','firstDay',
            'weekNumbers','showsTime','timeFormat','date'
            );

    /**
     * Checks to see if the calendar parameter is set on the field
     *
     * @param    k            current field name
     * @param    curval        current value of field (set to null for default)
     *
     * If the calendar parameter is set on the field, this function displays
     * the button. It then pushes the Calendar.setup parameters into an array,
     * including the user specified ones in the calling program is they exist.
     * This array is then added to the $calendars array indexed by the field
     * name. This allows for multiple fields with calendar popups.
     */
    function calPopup_helper($k, $curval) /* {{{ */
    {
        if (@$this->fdd[$k]['calendar']) {
            $cal_ar['ifFormat']    = '%Y-%m-%d %H:%M';
            $cal_ar['showsTime']   = true;
            $cal_ar['singleClick'] = false;
            if (isset($curval)) {
                if (substr($curval, 0, 4) != '0000')
                    $cal_ar['date'] = $curval;
            }
            if (isset($this->fdd[$k]['strftimemask'])) {
                $cal_ar['ifFormat'] = $this->fdd[$k]['strftimemask'];
            }
            if (is_array($this->fdd[$k]['calendar'])) {
                foreach($this->fdd[$k]['calendar'] as $ck => $cv) {
                    $cal_ar[$ck] = $cv;
                }
            }
            $cal_ar['button'] = $this->dhtml['prefix'].'calbutton_'.$this->fds[$k];
            $this->calendars[$this->fds[$k]] = $cal_ar;

            $label = @$this->fdd[$k]['calendar']['label'];
            strlen($label) || $label = '...';

            $do_button = true;
            if (isset($this->fdd[$k]['calendar']['button'])) {
                $do_button = $this->fdd[$k]['calendar']['button'];
            };

               if ($do_button) {
                echo '<button id="',$cal_ar['button'],'">',$label,'</button>';
               } else {
                echo '<span style="cursor: pointer" id="',$cal_ar['button'],'">',$label,'</span>';
            }
        }
    } /* }}} */

    function htmlTextarea($name, $css, $k, $value = null, $js = NULL)
    {
        // mce_cal mod start
        if (isset($this->fdd[$k]['textarea']['html'])) {
            $mce_tag = 'mceEditor';
            if (is_string($this->fdd[$k]['textarea']['html'])) {
                $mce_tag = $this->fdd[$k]['textarea']['html'];
            };
            $css .= ' '.$mce_tag.' ';
        };
        // mce_cal mod end
        $ret = '<textarea class="'.$css.'" name="'.$name.'"';
        $ret .= ($this->readonly($k) ? ' disabled' : '');
        if (intval($this->fdd[$k]['textarea']['rows']) > 0) {
            $ret .= ' rows="'.$this->fdd[$k]['textarea']['rows'].'"';
        }
        if (intval($this->fdd[$k]['textarea']['cols']) > 0) {
            $ret .= ' cols="'.$this->fdd[$k]['textarea']['cols'].'"';
        }
        if (isset($this->fdd[$k]['textarea']['wrap'])) {
            $ret .= ' wrap="'.$this->fdd[$k]['textarea']['wrap'].'"';
        } else {
            $ret .= ' wrap="virtual"';
        }
        // mce_cal mod start
        if (isset($this->fdd[$k]['textarea']['html'])) {
            $mce_tag = 'editable';
            if (is_string($this->fdd[$k]['textarea']['html'])) {
                $mce_tag = $this->fdd[$k]['textarea']['html'];
            };
            $ret .= ' mce_'.$mce_tag.'=true ';
        };
        // mce_cal mod end
        $ret .= '>';
        if($escape) $ret .= htmlspecialchars($value);
        else $ret .= $value;
        $ret .= '</textarea>'."\n";
        return $ret;
    } /* }}} */

    function display_add_record() /* {{{ */
    {
        for ($tab = 0, $k = 0; $k < $this->num_fds; $k++) {
            if (isset($this->fdd[$k]['tab']) && $this->tabs_enabled() && $k > 0) {
                $tab++;
                echo '</table>',"\n";
                echo '</div>',"\n";
                echo '<div id="'.$this->dhtml['prefix'].'tab',$tab,'">',"\n";
                echo '<table class="',$this->getCSSclass('main'),'" summary="',$this->tb,'">',"\n";
            }
            if (! $this->displayed[$k]) {
                continue;
            }
            if ($this->hidden($k)) {
                echo $this->htmlHiddenData($this->fds[$k], $this->fdd[$k]['default']);
                continue;
            }
            $css_postfix    = @$this->fdd[$k]['css']['postfix'];
            $css_class_name = $this->getCSSclass('input', null, 'next', $css_postfix);
            $escape            = isset($this->fdd[$k]['escape']) ? $this->fdd[$k]['escape'] : true;
            echo '<tr class="',$this->getCSSclass('row', null, true, $css_postfix),'">',"\n";
            echo '<td class="',$this->getCSSclass('key', null, true, $css_postfix),'">';
            echo $this->fdd[$k]['name'],'</td>',"\n";
            echo '<td class="',$this->getCSSclass('value', null, true, $css_postfix),'"';
            echo $this->getColAttributes($k),">\n";
            if ($this->col_has_values($k)) {
                $vals       = $this->set_values($k);
                $selected   = @$this->fdd[$k]['default'];
                $multiple   = $this->col_has_multiple($k);
                $readonly   = $this->readonly($k);
                $strip_tags = true;
                //$escape     = true;
                if ($this->col_has_checkboxes($k) || $this->col_has_radio_buttons($k)) {
                    echo $this->htmlRadioCheck($this->cgi['prefix']['data'].$this->fds[$k],
                            $css_class_name, $vals, $selected, $multiple, $readonly,
                            $strip_tags, $escape);
                } else {
                    echo $this->htmlSelect($this->cgi['prefix']['data'].$this->fds[$k],
                            $css_class_name, $vals, $selected, $multiple, $readonly,
                            $strip_tags, $escape);
                }
            } elseif (isset ($this->fdd[$k]['textarea'])) {
                echo $this->htmlTextarea($this->cgi['prefix']['data'].$this->fds[$k], $css_class_name, $k, $this->fdd[$k]['default']);
            } elseif ($this->col_has_php($k)) {
                echo include($this->fdd[$k]['php']);
            } else {
                // Simple edit box required
                $len_props = '';
                $maxlen = intval($this->fdd[$k]['maxlen']);
                $size   = isset($this->fdd[$k]['size']) ? $this->fdd[$k]['size'] : min($maxlen, 60);
                if ($size > 0) {
                    $len_props .= ' size="'.$size.'"';
                }
                if ($maxlen > 0) {
                    $len_props .= ' maxlength="'.$maxlen.'"';
                }
                echo '<input class="',$css_class_name,'" ';
                echo ($this->password($k) ? 'type="password"' : 'type="text"');
                echo ($this->readonly($k) ? ' disabled' : '');
                /* mce_cal mod start */
                echo ' id="',$this->dhtml['prefix'].'fld_'.$this->fds[$k],'"';
                /* mce_cal mod end */
                echo ' name="',$this->cgi['prefix']['data'].$this->fds[$k],'"';
                echo $len_props,' value="';
                if($escape) echo htmlspecialchars($this->fdd[$k]['default']);
                else echo $this->fdd[$k]['default'];
                echo '" />';
                /* mce_cal mod start */
                /* Call htmlcal helper function */
                $this->calPopup_helper($k, null);
                /* mce_cal mod end */
            }
            echo '</td>',"\n";
            if ($this->guidance) {
                $css_class_name = $this->getCSSclass('help', null, true, $css_postfix);
                $cell_value     = $this->fdd[$k]['help'] ? $this->fdd[$k]['help'] : '&nbsp;';
                echo '<td class="',$css_class_name,'">',$cell_value,'</td>',"\n";
            }
            echo '</tr>',"\n";
        }
    } /* }}} */

    function display_change_field($row, $k) /* {{{ */
    {
        $css_postfix    = @$this->fdd[$k]['css']['postfix'];
        $css_class_name = $this->getCSSclass('input', null, true, $css_postfix);
        $escape         = isset($this->fdd[$k]['escape']) ? $this->fdd[$k]['escape'] : true;
        echo '<td class="',$this->getCSSclass('value', null, true, $css_postfix),'"';
        echo $this->getColAttributes($k),">\n";
        if ($this->col_has_values($k)) {
            $vals       = $this->set_values($k);
            $multiple   = $this->col_has_multiple($k);
            $readonly   = $this->readonly($k);
            $strip_tags = true;
            //$escape     = true;
            if ($this->col_has_checkboxes($k) || $this->col_has_radio_buttons($k)) {
                echo $this->htmlRadioCheck($this->cgi['prefix']['data'].$this->fds[$k],
                        $css_class_name, $vals, $row["qf$k"], $multiple, $readonly,
                        $strip_tags, $escape);
            } else {
                echo $this->htmlSelect($this->cgi['prefix']['data'].$this->fds[$k],
                        $css_class_name, $vals, $row["qf$k"], $multiple, $readonly,
                        $strip_tags, $escape);
            }
        } elseif (isset($this->fdd[$k]['textarea'])) {
            echo $this->htmlTextarea($this->cgi['prefix']['data'].$this->fds[$k], $css_class_name, $k, $row["qf$k"]);
        } elseif ($this->col_has_php($k)) {
            echo include($this->fdd[$k]['php']);
        } else {
            $len_props = '';
            $maxlen = intval($this->fdd[$k]['maxlen']);
            $size   = isset($this->fdd[$k]['size']) ? $this->fdd[$k]['size'] : min($maxlen, 60);
            if ($size > 0) {
                $len_props .= ' size="'.$size.'"';
            }
            if ($maxlen > 0) {
                $len_props .= ' maxlength="'.$maxlen.'"';
            }
            echo '<input class="',$css_class_name,'" type="text"';
            echo ($this->readonly($k) ? ' disabled' : '');
            /* mce_cal mod start */
            echo ' id="',$this->dhtml['prefix'].'fld_'.$this->fds[$k],'"';
            /* mce_cal mod end */
            echo ' name="',$this->cgi['prefix']['data'].$this->fds[$k],'" value="';
            if($escape) echo htmlspecialchars($row["qf$k"]);
            else echo $row["qf$k"];
            echo '"',$len_props,' />',"\n";
            /* mce_cal mod start */
            /* Call calPopup helper function */
            $this->calPopup_helper($k, htmlspecialchars($row["qf$k"]));
            /* mce_cal mod end */
        }
        echo '</td>',"\n";
    } /* }}} */

    function form_end() /* {{{ */
    {
        if ($this->display['form']) {
            echo '</form>',"\n";

            /* mce_cal mod start */

            /* Add script calls to the end of the form for all fields
               with calendar popups. */
            if (isset($this->calendars)) {
                echo '<script type="text/javascript"><!--',"\n";
                foreach($this->calendars as $ck => $cv) {
                    echo 'Calendar.setup({',"\n";
                    foreach ($cv as $ck1 => $cv1) {
                        if (in_array($ck1, $this->valid_opts)) {
                            echo "\t",str_pad($ck1, 15),' : "',$cv1,'",',"\n";
                        }
                    }
                    echo "\t",str_pad('inputField', 15),' : "',$this->dhtml['prefix'].'fld_'.$ck,'"',"\n";
                    echo '});',"\n";
                };
                echo '// -->',"\n";
                echo '</script>',"\n";
            };
            /* mce_cal mod end */
        };
    } /* }}} */

}
