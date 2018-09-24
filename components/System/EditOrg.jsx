import React from 'react';
import PropTypes from 'prop-types';
import { mapEditOrgProps, mapEditOrgDispatch } from '../maps/System/EditOrg.map';
import { connect } from 'react-redux';
import { DuxOkDialog } from 'duxpanel';
import { DuxForm, DuxInput } from 'duxform';

const EditOrgUi = props => {
    const opts = props.timezones.map(tz => <option key={tz} value={tz}>{tz}</option>);

    return (
        <DuxOkDialog show={props.show}
                     title="Edit Organization"
                     okClassName="btn btn-primary"
                     cancelClassName="btn btn-warning"
                     onOk={props.okClicked}
                     onCancel={props.cancelClicked}
                     showCancel={true}
                     shouldClose={() => props.formValid}
        >
            <DuxForm name="editorg">
                <div className="form-group">
                    <label>Organization Name</label>
                    <DuxInput name="orgname"
                              className={'form-control' + (props.orgNameValid ? '' : ' is-invalid')}
                              maxLength={30}
                              defaultValue={props.orgName}
                              onValidate={value => value.length < 1 ? 'Organization name required' : undefined}
                    />
                    <small style={{height:'1em'}} className={'text-danger' + (props.orgNameValid ? ' invisible' : '')}>
                        Organization name required
                    </small>
                </div>
                <div className="form-group">
                    <label>Default Timezone</label>
                    <DuxInput name="defaulttz" type="select" className="form-control" defaultValue={props.defaultTZ}>{opts}</DuxInput>
                </div>
                <div className="form-group">
                    <label>Tag</label>
                    <DuxInput name="tag" maxLength={15} className="form-control" defaultValue={props.tag} forceUpper={true}/>
                </div>
            </DuxForm>
        </DuxOkDialog>
    );
};

EditOrgUi.propTypes = {
    formValid: PropTypes.bool.isRequired,
    orgNameValid: PropTypes.bool.isRequired,
    show: PropTypes.bool.isRequired,
    timezones: PropTypes.arrayOf(PropTypes.string).isRequired,
    orgName: PropTypes.string.isRequired,
    defaultTZ: PropTypes.string.isRequired,
    tag: PropTypes.string.isRequired,

    cancelClicked: PropTypes.func.isRequired,
    okClicked: PropTypes.func.isRequired
};

export const EditOrg = connect(mapEditOrgProps, mapEditOrgDispatch)(EditOrgUi);
