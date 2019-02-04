import React from 'react';
import PropTypes from 'prop-types';
import { mapSystemUsersProps, mapSystemUsersDispatch } from '../maps/System/SystemUsers.map';
import { connect } from 'react-redux';
import { DuxTable } from 'duxtable';
import { DuxYesNoDialog } from 'duxpanel';

class SystemUsersUi extends React.Component {
    componentDidMount() {
        this.props.init();
    }

    selectedRender = (item, colNum) => {
        if (colNum !== -1) {
            return undefined;
        }

        return (
            <div className="mt-2">
                <button type="button" className="btn btn-sm ml-2 btn-danger" onClick={() => this.props.deleteUserClicked(item.UserId)}>Delete User</button>
                { item.FillMemberId !== 0 &&
                    <button type="button" className="btn btn-sm ml-2" onClick={() => this.props.fillNameClicked(item.FillMemberId)}>Fill Name</button>
                }
            </div>
        );
    };

    render() {
        const columns = [
            {
                field: 'UserName',
                title: 'Name'
            },
            {
                field: 'OrgNames',
                title: 'Organizations'
            }
        ];

        return (
            <div>
                <DuxTable name="systemusers"
                          columns={columns}
                          striped={true}
                          data={this.props.users}
                          rowKey="UserId"
                          sortColumn={0}
                          fetchingData={this.props.fetching}
                          fetchingMsg="Retrieving Users"
                          emptyMsg="No Users Found"
                          selectionMode="single"
                          selectedRenderCallback={this.selectedRender}
                />

                <DuxYesNoDialog show={this.props.showDeleteDialog}
                                title="Confirm Delete User"
                                noClassName="btn"
                                yesClassName="btn btn-danger"
                                onNo={this.props.deleteNoClicked}
                                onYes={this.props.deleteYesClicked}
                >
                    Are you sure you want to delete this user?
                </DuxYesNoDialog>
            </div>
        );
    }
}

SystemUsersUi.propTypes = {
    users: PropTypes.array.isRequired,
    fetching: PropTypes.bool.isRequired,
    showDeleteDialog: PropTypes.bool.isRequired,
    deleteUserId: PropTypes.number.isRequired,

    init: PropTypes.func.isRequired,
    deleteUserClicked: PropTypes.func.isRequired,
    deleteYesClicked: PropTypes.func.isRequired,
    deleteNoClicked: PropTypes.func.isRequired,
    fillNameClicked: PropTypes.func.isRequired
};

export const SystemUsers = connect(mapSystemUsersProps, mapSystemUsersDispatch)(SystemUsersUi);
