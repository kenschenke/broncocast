import React from 'react';
import PropTypes from 'prop-types';
import { mapAdminGroupsProps, mapAdminGroupsDispatch } from '../maps/Admin/AdminGroups.map';
import { connect } from 'react-redux';
import { InputDialog } from '../InputDialog.jsx';
import { DuxYesNoDialog } from 'duxpanel';
import { DuxForm, DuxInput } from 'duxform';
import { AdminGroupMembers } from './AdminGroupMembers.jsx';

class AdminGroupsUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            nameInitialValue: '',  // initial value for name change dialog
            adminOrgId: 0,
            selectedGroupId: 0,
            showMembersDialog: false
        };
    }

    componentDidMount() {
        this.props.adminOrgChanged();
    }

    componentDidUpdate() {
        if (this.props.adminOrgId !== this.state.adminOrgId) {
            this.props.adminOrgChanged();
            this.setState({adminOrgId: this.props.adminOrgId});
        }
    }

    changeNameClicked = (GroupId, Name) => {
        this.setState({nameInitialValue: Name});

        this.props.changeNameClicked(GroupId);
    };

    render() {
        const groups = this.props.groups.map(group => {
            return (
                <li key={group.GroupId}
                    className={'list-group-item' + (group.GroupId === this.state.selectedGroupId ? ' list-group-item-secondary' : '')}
                    style={{cursor:'pointer'}}
                    onClick={() => this.setState({selectedGroupId:group.GroupId})}
                >
                    {group.GroupName}
                    { group.GroupId === this.state.selectedGroupId &&
                        <div className="mt-2">
                            <button type="button" className="btn btn-sm btn-secondary ml-2" onClick={() => this.setState({showMembersDialog:true})}>Members</button>
                            <button type="button" className="btn btn-sm btn-secondary ml-2" onClick={() => this.changeNameClicked(group.GroupId,group.GroupName)}>Rename</button>
                            <button type="button" className="btn btn-sm btn-danger ml-2" onClick={() => this.props.removeGroup(group.GroupId)}>Remove</button>
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
                                {groups}
                            </ul>

                            <DuxForm name="newgroup" className="mt-3">
                                <div className="input-group">
                                    <DuxInput name="name"
                                              className="form-control"
                                              placeholder="New group name"
                                              onValidate={name => name.length < 1 ? 'Group name required' : undefined}
                                    />
                                    <div className="input-group-append">
                                        <button className="btn btn-secondary"
                                                onClick={this.props.addGroupClicked}
                                                disabled={!this.props.newGroupNameValid}
                                        >
                                            Add Group
                                        </button>
                                    </div>
                                </div>
                            </DuxForm>
                        </div>
                    </div>
                </div>

                <InputDialog show={this.props.showChangeNameDialog}
                             title="Change Group Name"
                             label="Group Name"
                             placeholder="Enter group name"
                             onValidate={name => name.length < 1 ? 'Group name required' : undefined}
                             onOk={this.props.changeNameOk}
                             onCancel={this.props.changeNameCancel}
                             initialValue={this.state.nameInitialValue}
                />

                <DuxYesNoDialog show={this.props.showRemoveDialog}
                                title="Confirm Remove Group"
                                noClassName="btn"
                                yesClassName="btn btn-danger"
                                onNo={this.props.removeDialogNo}
                                onYes={this.props.removeDialogYes}
                >
                    Are you sure you want to remove this group?
                </DuxYesNoDialog>

                { this.state.showMembersDialog &&
                    <AdminGroupMembers GroupId={this.state.selectedGroupId}
                                       closeClicked={() => this.setState({showMembersDialog:false})}
                    />
                }
            </div>
        );
    }
}

AdminGroupsUi.propTypes = {
    adminOrgId: PropTypes.number.isRequired,
    fetching: PropTypes.bool.isRequired,
    groups: PropTypes.array.isRequired,
    showChangeNameDialog: PropTypes.bool.isRequired,
    showRemoveDialog: PropTypes.bool.isRequired,
    newGroupNameValid: PropTypes.bool.isRequired,

    adminOrgChanged: PropTypes.func.isRequired,
    changeNameClicked: PropTypes.func.isRequired,
    changeNameOk: PropTypes.func.isRequired,
    changeNameCancel: PropTypes.func.isRequired,
    removeDialogNo: PropTypes.func.isRequired,
    removeDialogYes: PropTypes.func.isRequired,
    removeGroup: PropTypes.func.isRequired,
    addGroupClicked: PropTypes.func.isRequired
};

export const AdminGroups = connect(mapAdminGroupsProps, mapAdminGroupsDispatch)(AdminGroupsUi);
