import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { DuxForm, DuxInput, isFieldValidOrPristine, isFormValid } from 'duxform';
import { DuxOkDialog } from 'duxpanel';

const mapProps = state => {
    return {
        emailValid: isFieldValidOrPristine(state, 'editemail', 'email'),
        formValid: isFormValid(state, 'editemail')
    };
};

const EditEmailUi = props => {
    return (
        <DuxOkDialog show={props.show}
                     title="Edit Email Address"
                     cancelClassName="btn btn-secondary"
                     okClassName="btn btn-secondary"
                     onCancel={props.cancelClicked}
                     onOk={props.okClicked}
                     shouldClose={() => props.formValid}
                     showCancel={true}
                     width={{xs:'90%',sm:400}}
                     top={{xs:'5%',sm:50}}
        >
            <DuxForm name="editemail">
                <div className="form-group">
                    <label>Email Address</label>
                    <DuxInput name="email"
                              className={'form-control' + (props.emailValid ? '' : ' is-invalid')}
                              placeholder="Enter email"
                              defaultValue={props.email}
                              onValidate={email => email.search(/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/) >= 0 ? undefined : 'Invalid email address'}
                    />
                    <small className={'text-danger ' + (props.emailValid ? 'invisible' : 'visible')} style={{height:'1em'}}>
                        Please enter a valid email address
                    </small>
                </div>
            </DuxForm>
        </DuxOkDialog>
    );
};

EditEmailUi.propTypes = {
    show: PropTypes.bool.isRequired,
    email: PropTypes.string.isRequired,
    emailValid: PropTypes.bool.isRequired,
    formValid: PropTypes.bool.isRequired,

    cancelClicked: PropTypes.func.isRequired,
    okClicked: PropTypes.func.isRequired
};

export const EditEmail = connect(mapProps)(EditEmailUi);
