import React from 'react';
import PropTypes from 'prop-types';
import { DuxOkDialog } from 'duxpanel';
import { connect } from 'react-redux';
import { DuxForm, DuxInput, isFieldValid, isFieldValidOrPristine, getFormFieldValue, getFormFieldError } from 'duxform';

const mapProps = state => {
    return {
        isValid: isFieldValid(state, 'inputform', 'inputfield'),
        isFieldValidOrPristine: isFieldValidOrPristine(state, 'inputform', 'inputfield'),
        inputValue: getFormFieldValue(state, 'inputform', 'inputfield', ''),
        inputError: getFormFieldError(state, 'inputform', 'inputfield')
    };
};

const InputDialogUi = props => {
    return (
        <DuxOkDialog
            name="inputdialog"
            title={props.title}
            show={props.show}
            cancelClassName="btn btn-warning"
            okClassName="btn btn-primary"
            onCancel={props.onCancel}
            onOk={() => props.onOk(props.inputValue)}
            shouldClose={() => props.isValid}
            showCancel={true}
        >
            <DuxForm name="inputform" initialFocus="inputfield">
                <div className="form-group">
                    <label>{props.label}</label>
                    <DuxInput name="inputfield"
                              className={'form-control' + (props.isFieldValidOrPristine ? '' : ' is-invalid')}
                              placeholder={props.placeholder}
                              defaultValue={props.initialValue}
                              onValidate={props.onValidate}
                    />
                    <small className={'text-danger' + (props.isFieldValidOrPristine ? ' invisible' : '')}>
                        {props.inputError.length ? props.inputError : '&nbsp;'}
                    </small>
                </div>
            </DuxForm>
        </DuxOkDialog>
    );
};

InputDialogUi.propTypes = {
    // Provided by parent
    show: PropTypes.bool.isRequired,
    title: PropTypes.string.isRequired,   // Dialog title
    label: PropTypes.string.isRequired,   // Input field label
    placeholder: PropTypes.string,        // Input field placeholder
    onValidate: PropTypes.func,           // Input validate
    onOk: PropTypes.func.isRequired,      // Called with input value
    onCancel: PropTypes.func.isRequired,
    initialValue: PropTypes.string.isRequired,

    // Provided by Redux store
    isValid: PropTypes.bool.isRequired,
    isFieldValidOrPristine: PropTypes.bool.isRequired,
    inputValue: PropTypes.string.isRequired,
    inputError: PropTypes.string.isRequired
};

export const InputDialog = connect(mapProps)(InputDialogUi);
