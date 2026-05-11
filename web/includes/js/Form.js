//********************************************************************//
// Binder functions
//********************************************************************//
function Form_bind_event(id, type, code)
{
    if (id == "window")
    {
        Form_bind_event_to_window(code);
    }
    else
    {
        element_function = function()
        {
            var element    = document.getElementById(id);
            var event_name = "on" + type;
            var old_event  = element[event_name];

            if (typeof element[event_name] != "function")
            {
                element[event_name] = code;
            }
            else
            {
                element[event_name] = function()
                {
                    if (old_event)
                    {
                        if (old_event())
                        {
							return code();
						}
						else
						{
							return false;
						}
                    }
                    else
                    {
						return code();
					}
                }
            }
        }
        Form_bind_event_to_window(element_function);
    }
}

function Form_bind_event_to_window(code)
{
    var old_event = window.onload;
    
    if (typeof window.onload != "function")
    {
        window.onload = code;
    }
    else
    {
        window.onload = function()
        {
            if (old_event)
            {
                old_event();
            }
            code();
        }
    }
}

//********************************************************************//
// FormElementFieldset functions
//********************************************************************//
function FormFieldset_collapse(fieldset_id)
{
    var fieldset  = document.getElementById(fieldset_id);
    var collapsed = document.getElementById("FIELDSET-" + fieldset_id + "-COLLAPSED");
    var fields    = fieldset.getElementsByTagName("div");

    for (var i = 0; i < fields.length; i++)
    {
		if (fields[i].className == "form-error-group" || fields[i].className == "form-individual-error")
		{
			continue;
		}

        if (fields[i].style != null && fields[i].style.display != null && fields[i].style.display != "none")
        {
            fields[i].style.display = "none";
            fieldset.className      = fieldset.className.replace("fieldset_expanded", "fieldset_collapsed");
            collapsed.value         = 1;
        }
        else
        {
            fields[i].style.display = "block";
            fieldset.className      = fieldset.className.replace("fieldset_collapsed", "fieldset_expanded");
            collapsed.value         = 0;
        }
    }
    
    return false;
}

//********************************************************************//
// Validator functions
//********************************************************************//
var FormErrorFields  = Array();
var FormSubmitButton = Array();

function Form_show_error(field_id, field_type)
{
	Form_toggle_error(field_id, field_type, "show");
}

function Form_hide_error(field_id, field_type)
{
	Form_toggle_error(field_id, field_type, "hide");
}

function Form_toggle_error(field_id, field_type, which)
{
	var parent_form = document.getElementById(field_id).form;

	if (!(parent_form.id in FormErrorFields))
	{
		FormErrorFields[parent_form.id]                       = Array();
		FormErrorFields[parent_form.id][field_id]             = Array();
		FormErrorFields[parent_form.id][field_id][field_type] = false;
	}
	else
	{
		if (!(field_id in FormErrorFields[parent_form.id]))
		{
			FormErrorFields[parent_form.id][field_id]             = Array();
			FormErrorFields[parent_form.id][field_id][field_type] = false;
		}
		else
		{
			if (!(field_type in FormErrorFields[parent_form.id][field_id]))
			{
				FormErrorFields[parent_form.id][field_id][field_type] = false;
			}
		}
	}

	if (which == "show")
	{
		document.getElementById("form-individual-error-" + field_type + "-" + field_id).style.display = "block";
		document.getElementById("form-error-group-" + field_id).style.display 						  = "block";
		FormErrorFields[parent_form.id][field_id][field_type] 										  = true;
	}
	else
	{
		document.getElementById("form-individual-error-" + field_type + "-" + field_id).style.display = "none";
		FormErrorFields[parent_form.id][field_id][field_type] 										  = false;
	}

	var overall_errors = false;
	var field_errors   = false;
	for (var field in FormErrorFields[parent_form.id])
	{
		field_errors = false;
		for (var type in FormErrorFields[parent_form.id][field])
		{
			if (FormErrorFields[parent_form.id][field][type])
			{
				field_errors = true;
				break;
			}
		}

		if (field_errors)
		{
			overall_errors = true;
//			break;
		}
		else
		{
			document.getElementById("form-error-group-" + field).style.display = "none";
		}
	}

	if (overall_errors)
	{
		for (var i = 0; i < FormSubmitButton.length; i++)
		{
			if (document.getElementById(FormSubmitButton[i]).form.id == document.getElementById(field_id).form.id)
			{
				document.getElementById(FormSubmitButton[i]).disabled = true;
			}
		}
	}
	else
	{
		for (var i = 0; i < FormSubmitButton.length; i++)
		{
			if (document.getElementById(FormSubmitButton[i]).form.id == document.getElementById(field_id).form.id)
			{
				document.getElementById(FormSubmitButton[i]).disabled = false;
			}
		}
	}
}

function Form_validate_existance(field_id, show_error)
{
	var field = document.getElementById(field_id);
	var error = false;

    if (field.value == null || field.value == "")
    {
        error = true;
    }

	if (error)
	{
		if (show_error)
		{
			Form_show_error(field_id, "existance");
		}

		return false;
	}
	else
	{
		if (show_error)
		{
			Form_hide_error(field_id, "existance");
		}

		return true;
	}
}

function Form_validate_file_type(field_id, file_types)
{
	var error = false;

    if (Form_validate_existance(field_id, false))
    {
        var parts      = document.getElementById(field_id).value.split(".");
        var type       = parts[parts.length - 1].toLowerCase();
		var type_found = false;

        for (var i = 0; i < file_types.length; i++)
        {
            if (type == file_types[i])
            {
                type_found = true;
            }
        }
        
        if (!type_found)
        {
			error = true
		}
    }
    
	if (error)
	{
		Form_show_error(field_id, "file_type");

		return false;
	}
	else
	{
		Form_hide_error(field_id, "file_type");

		return true;
	}
}

function Form_validate_length_long(field_id, field_length)
{
	var error = false;

    if (Form_validate_existance(field_id, false))
    {
        if (document.getElementById(field_id).value.length > field_length)
        {
            error = true;
        }
    }

	if (error)
	{
		Form_show_error(field_id, "length_long");

		return false;
	}
	else
	{
		Form_hide_error(field_id, "length_long");

		return true;
	}
}

function Form_validate_length_short(field_id, field_length)
{
	var error = false;

    if (Form_validate_existance(field_id, false))
    {
        if (document.getElementById(field_id).value.length < field_length)
        {
            error = true;
        }
    }

	if (error)
	{
		Form_show_error(field_id, "length_short");

		return false;
	}
	else
	{
		Form_hide_error(field_id, "length_short");

		return true;
	}
}

function Form_validate_match(field_id, other_field_id, sequence, match)
{
	var error = false;

    if (Form_validate_existance(field_id, false))
    {
		if (match)
		{
			if (document.getElementById(field_id).value != document.getElementById(other_field_id).value)
			{
				error = true;
			}
		}
		else
		{
			if (document.getElementById(field_id).value == document.getElementById(other_field_id).value)
			{
				error = true;
			}
		}
    }

	if (error)
	{
		Form_show_error(field_id, "match" + sequence);

		return false;
	}
	else
	{
		Form_hide_error(field_id, "match" + sequence);

		return true;
	}
}

function Form_validate_maximum_checked(field_id, maximum_checked)
{
	var error       = false;
	var checkboxes  = document.getElementById("form-element-" + field_id).getElementsByTagName("input");
	var num_checked = 0;

	for (var i = 0; i < checkboxes.length; i++)
	{
		if (checkboxes[i].checked)
		{
			num_checked++;
		}
	}

	if (num_checked > maximum_checked)
	{
		Form_show_error(field_id, "maximum_checked");

		return false;
	}
	else
	{
		Form_hide_error(field_id, "maximum_checked");

		return true;
	}
}

function Form_validate_maximum_selected(field_id, maximum_selected)
{
	var error        = false;
	var select_field = document.getElementById(field_id);
	var num_selected = 0;

	for (var i = 0; i < select_field.length; i++)
	{
		if (select_field[i].selected)
		{
			num_selected++;
		}
	}

	if (num_selected > maximum_selected)
	{
		Form_show_error(field_id, "maximum_selected");

		return false;
	}
	else
	{
		Form_hide_error(field_id, "maximum_selected");

		return true;
	}
}

function Form_validate_minimum_checked(field_id, minimum_checked)
{
	var error       = false;
	var checkboxes  = document.getElementById("form-element-" + field_id).getElementsByTagName("input");
	var num_checked = 0;

	for (var i = 0; i < checkboxes.length; i++)
	{
		if (checkboxes[i].checked)
		{
			num_checked++;
		}
	}

	if (num_checked < minimum_checked)
	{
		Form_show_error(field_id, "minimum_checked");

		return false;
	}
	else
	{
		Form_hide_error(field_id, "minimum_checked");

		return true;
	}
}

function Form_validate_minimum_selected(field_id, minimum_selected)
{
	var error        = false;
	var select_field = document.getElementById(field_id);
	var num_selected = 0;

	for (var i = 0; i < select_field.length; i++)
	{
		if (select_field[i].selected)
		{
			num_selected++;
		}
	}

	if (num_selected < minimum_selected)
	{
		Form_show_error(field_id, "minimum_selected");

		return false;
	}
	else
	{
		Form_hide_error(field_id, "minimum_selected");

		return true;
	}
}

function Form_validate_pattern(field_id, sequence, regex_pattern, match)
{
	var error = false;

    if (Form_validate_existance(field_id, false))
    {
		var field = document.getElementById(field_id);

		if (match)
		{
			if (!regex_pattern.test(field.value))
			{
				error = true;
			}
		}
		else
		{
			if (regex_pattern.test(field.value))
			{
				error = true;
			}
		}
    }
    
	if (error)
	{
		Form_show_error(field_id, "pattern" + sequence);

		return false;
	}
	else
	{
		Form_hide_error(field_id, "pattern" + sequence);

		return true;
	}
}
