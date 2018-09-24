import React from 'react';
import PropTypes from 'prop-types';
import { mapSystemOrgsProps, mapSystemOrgsDispatch } from '../maps/System/SystemOrgs.map';
import { connect } from 'react-redux';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import faEdit from '@fortawesome/fontawesome-free-solid/faEdit';
import faTrash from '@fortawesome/fontawesome-free-solid/faTrash';
import { EditOrg } from './EditOrg.jsx';
import { DuxYesNoDialog } from 'duxpanel';

class SystemOrgsUi extends React.Component {
    componentDidMount() {
        this.props.init();
    }

    render() {
        const orgs = this.props.orgs.map(org => {
            return (
                <li key={org.OrgId}
                    className={'list-group-item' + (org.OrgId === this.props.selectedOrgId ? ' list-group-item-secondary' : '')}
                    style={{cursor:'pointer'}}
                    onClick={() => this.props.orgSelected(org.OrgId)}
                >
                    <div className="float-left">{org.OrgName}</div>
                    { org.OrgId === this.props.selectedOrgId &&
                    <div className="float-right">
                        <span className="text-secondary" onClick={this.props.editOrgClicked}><FontAwesomeIcon icon={faEdit}/></span>
                        <span className="text-danger ml-3" onClick={this.props.deleteOrgClicked}><FontAwesomeIcon icon={faTrash}/></span>
                    </div>
                    }
                </li>
            );
        });

        return (
            <div>
                <div className="container">
                    <div className="row justify-content-md-center">
                        <div className="col col-md-8 col-lg-6">
                            <ul className="list-group">
                                {orgs}
                            </ul>

                            <button type="button" className="mt-2 btn btn-secondary" onClick={this.props.newOrgClicked}>New Organization</button>
                        </div>
                    </div>
                </div>

                <EditOrg/>

                <DuxYesNoDialog show={this.props.showDeleteDialog}
                                title="Delete Organization"
                                noClassName="btn"
                                yesClassName="btn btn-danger"
                                onYes={this.props.deleteYesClicked}
                                onNo={this.props.deleteNoClicked}
                >
                    Are you sure you want to delete this organization?
                </DuxYesNoDialog>
            </div>
        );
    }
}

SystemOrgsUi.propTypes = {
    orgs: PropTypes.array.isRequired,
    selectedOrgId: PropTypes.number.isRequired,
    showDeleteDialog: PropTypes.bool.isRequired,

    deleteOrgClicked: PropTypes.func.isRequired,
    deleteNoClicked: PropTypes.func.isRequired,
    deleteYesClicked: PropTypes.func.isRequired,
    editOrgClicked: PropTypes.func.isRequired,
    init: PropTypes.func.isRequired,
    orgSelected: PropTypes.func.isRequired,
    newOrgClicked: PropTypes.func.isRequired
};

export const SystemOrgs = connect(mapSystemOrgsProps, mapSystemOrgsDispatch)(SystemOrgsUi);
