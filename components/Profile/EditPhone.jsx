import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { DuxForm, DuxInput, isFieldValidOrPristine, isFormValid } from 'duxform';
import { DuxOkDialog } from 'duxpanel';
import { formatPhoneNumber } from '../../util/util';

const mapProps = state => {
    return {
        phoneValid: isFieldValidOrPristine(state, 'editphone', 'phone'),
        formValid: isFormValid(state, 'editphone')
    };
};

const EditPhoneUi = props => {
    return (
        <DuxOkDialog show={props.show}
                     title="Edit Mobile Phone"
                     cancelClassName="btn btn-secondary"
                     okClassName="btn btn-secondary"
                     onCancel={props.cancelClicked}
                     onOk={props.okClicked}
                     shouldClose={() => props.formValid}
                     showCancel={true}
                     width={{xs:'90%',sm:400}}
                     top={{xs:'5%',sm:50}}
        >
            <DuxForm name="editphone">
                <div className="form-group">
                    <label>Phone Number</label>
                    <DuxInput name="phone"
                              className={'form-control' + (props.phoneValid ? '' : ' is-invalid')}
                              placeholder="Enter phone number"
                              defaultValue={props.phone}
                              normalize={value => value.replace(/[^0-9]/g, '')}
                              format={formatPhoneNumber}
                              onValidate={value => value.replace(/[^0-9]/g,'').length === 10 ? undefined : 'Enter your 10 digit phone number'}
                    />
                    <small className={'text-danger ' + (props.phoneValid ? 'invisible' : 'visible')} style={{height:'1em'}}>
                        Please enter your 10 digit phone number
                    </small>
                </div>
            </DuxForm>
        </DuxOkDialog>
    );
};

EditPhoneUi.propTypes = {
    show: PropTypes.bool.isRequired,
    phone: PropTypes.string.isRequired,
    phoneValid: PropTypes.bool.isRequired,
    formValid: PropTypes.bool.isRequired,

    cancelClicked: PropTypes.func.isRequired,
    okClicked: PropTypes.func.isRequired
};

export const EditPhone = connect(mapProps)(EditPhoneUi);
