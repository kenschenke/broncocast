import React from 'react';
import PropTypes from 'prop-types';
import { mapProfileOrgsProps, mapProfileOrgsDispatch } from '../maps/Profile/ProfileOrgs.map';
import { connect } from 'react-redux';
import { DuxForm, DuxInput } from 'duxform';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import faTrash from '@fortawesome/fontawesome-free-solid/faTrash';

class ProfileOrgsUi extends React.Component {
    componentDidMount() {
        this.props.init();
    }

    render() {
        const orgs = this.props.orgs.map(org => {
            return (
                <li key={org.MemberId} onClick={() => this.props.orgSelected(org.MemberId)} style={{cursor:'pointer'}} className={'list-group-item' + (org.MemberId === this.props.selectedOrgId ? ' list-group-item-dark' : '')}>
                    {org.OrgName + (org.IsAdmin ? ' (Admin)' : '')}
                    { org.MemberId === this.props.selectedOrgId &&
                        <div className="mt-2">
                            <button type="button" className="btn btn-sm btn-danger" onClick={() => this.props.deleteOrgClicked(org.MemberId)}><FontAwesomeIcon icon={faTrash}/> Delete</button>
                        </div>
                    }
                </li>
            );
        });

        return (
            <div>
                <h4>Organization Memberships</h4>
                { this.props.orgs.length > 0 &&
                <small className="text-info">Click or tap an organization for options.</small>
                }
                <ul className="list-group mt-2">
                    {orgs}
                </ul>
                <DuxForm name="userorgs">
                    <div className="form-group">
                        <label>Enter tag to join an organization</label>
                        <div className="input-group">
                            <DuxInput name="tag"
                                      className={'form-control' + (this.props.orgMessage.length < 1 ? '' : ' is-invalid')}
                                      maxLength={15}
                                      onValidate={value => value.length < 1 ? 'Tag cannot be empty' : undefined}
                            />
                            <div className="input-group-append">
                                <button className="btn btn-secondary" type="button" disabled={this.props.joinDisabled} onClick={this.props.addOrg}>Join</button>
                            </div>
                        </div>
                        <small className="text-danger" style={{height:'.75em'}}>
                            {this.props.orgMessage}
                        </small>
                    </div>
                </DuxForm>
            </div>
        );
    }
}

ProfileOrgsUi.propTypes = {
    orgs: PropTypes.array.isRequired,
    orgMessage: PropTypes.string.isRequired,
    selectedOrgId: PropTypes.number.isRequired,
    joinDisabled: PropTypes.bool.isRequired,

    addOrg: PropTypes.func.isRequired,
    deleteOrgClicked: PropTypes.func.isRequired,
    init: PropTypes.func.isRequired,
    orgSelected: PropTypes.func.isRequired
};

export const ProfileOrgs = connect(mapProfileOrgsProps, mapProfileOrgsDispatch)(ProfileOrgsUi);
