import React from 'react';
import PropTypes from 'prop-types';
import { mapAdminBroadcastsProps, mapAdminBroadcastsDispatch } from '../maps/Admin/AdminBroadcasts.map';
import { connect } from 'react-redux';
import { DuxTable } from 'duxtable';
import { ViewBroadcast } from '../ViewBroadcast.jsx';
import { NewBroadcast } from './NewBroadcast.jsx';

class AdminBroadcastsUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            adminOrgId: 0,
            showViewBroadcast: false,
            viewSentBy: '',
            viewTime: '',
            viewShortMsg: '',
            viewLongMsg: '',
            viewAttachmentUrl: '',
            viewRecipients: []
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

    selectedRowRender = (item, colNum) => {
        if (colNum === 3) {
            return (
                <div>
                    <div className="float-left" style={{width:'80%', overflow:'hidden', textOverflow:'ellipsis'}}>
                        {item.ShortMsg}
                    </div>
                    <div className="float-right">
                        <a href="#" className="text-light" onClick={e => this.viewBroadcast(e, item)}>
                            <span className="badge badge-light">View</span>
                        </a>
                    </div>
                </div>
            );
        }
    };

    viewBroadcast = (event, item) => {
        event.preventDefault();

        this.setState({
            showViewBroadcast: true,
            viewSentBy: item.UsrName,
            viewTime: item.Time,
            viewShortMsg: item.ShortMsg,
            viewLongMsg: item.LongMsg,
            viewAttachmentUrl: item.AttachmentUrl,
            viewRecipients: item.Recipients
        });
    };

    render() {
        const columns = [
            {
                title: 'Time',
                field: 'Time',
                sortCallback: (b1, b2, sortAscending) => {
                    if (b1.Timestamp > b2.Timestamp) {
                        return sortAscending ? 1 : -1;
                    } else if (b1.Timestamp < b2.Timestamp) {
                        return sortAscending ? -1 : 1;
                    } else {
                        return 0;
                    }
                },
                hidden: {
                    xs: true,
                    lg: false
                },
                width: 225
            },
            {
                title: 'Status',
                render: item => item.IsDelivered ? 'Delivered' : 'Scheduled',
                sortable: false,
                hidden: {
                    xs: true,
                    lg: false
                },
                width: 100
            },
            {
                title: 'Sent By',
                field: 'UsrName',
                hidden: {
                    xs: true,
                    lg: false
                },
                width: 150
            },
            {
                title: 'Message',
                field: 'ShortMsg'
            }
        ];

        return (
            <div>
                <button className="btn btn-sm btn-secondary mb-2" onClick={this.props.newBroadcastClicked}>New Broadcast</button>

                <DuxTable name="mybroadcasts"
                          columns={columns}
                          data={this.props.broadcasts}
                          sortColumn={0}
                          sortAscending={false}
                          rowKey="BroadcastId"
                          striped={true}
                          selectionMode="single"
                          selectedRenderCallback={this.selectedRowRender}
                          fetchingData={this.props.fetching}
                          fetchingMsg="Fetching Broadcasts"
                />

                <ViewBroadcast show={this.state.showViewBroadcast}
                               sentBy={this.state.viewSentBy}
                               delivered={this.state.viewTime}
                               shortMsg={this.state.viewShortMsg}
                               longMsg={this.state.viewLongMsg}
                               attachmentUrl={this.state.viewAttachmentUrl}
                               closeClicked={() => this.setState({showViewBroadcast:false})}
                               recipients={this.state.viewRecipients}
                />

                {this.props.showNewBroadcast && <NewBroadcast/>}
            </div>
        );
    }
}

AdminBroadcastsUi.propTypes = {
    adminOrgId: PropTypes.number.isRequired,
    fetching: PropTypes.bool.isRequired,
    broadcasts: PropTypes.array.isRequired,
    showNewBroadcast: PropTypes.bool.isRequired,

    adminOrgChanged: PropTypes.func.isRequired,
    newBroadcastClicked: PropTypes.func.isRequired
};

export const AdminBroadcasts = connect(mapAdminBroadcastsProps, mapAdminBroadcastsDispatch)(AdminBroadcastsUi);
