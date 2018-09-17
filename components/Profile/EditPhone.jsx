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
    const carrierOpts = window.Carriers.map(carrier => {
        return <option key={carrier.CarrierId} value={carrier.CarrierId}>{carrier.CarrierName}</option>;
    });
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
            <DuxForm name="editphone" onValidate={fields => parseInt(fields.carrier) !== 0 ? undefined : 'Please pick a carrier'}>
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
                <div className="form-group">
                    <label>Carrier</label>
                    <DuxInput name="carrier"
                              className="form-control"
                              type="select"
                              defaultValue={props.carrierId}
                    >
                        <option value={0}>(Pick One)</option>
                        {carrierOpts}
                    </DuxInput>
                </div>
            </DuxForm>
        </DuxOkDialog>
    );
};

EditPhoneUi.propTypes = {
    show: PropTypes.bool.isRequired,
    phone: PropTypes.string.isRequired,
    phoneValid: PropTypes.bool.isRequired,
    carrierId: PropTypes.number.isRequired,
    formValid: PropTypes.bool.isRequired,

    cancelClicked: PropTypes.func.isRequired,
    okClicked: PropTypes.func.isRequired
};

export const EditPhone = connect(mapProps)(EditPhoneUi);
