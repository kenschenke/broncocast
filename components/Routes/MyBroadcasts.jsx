import React from 'react';
import PropTypes from 'prop-types';
import { mapMyBroadcastsProps, mapMyBroadcastsDispatch } from '../maps/Routes/MyBroadcasts.map';
import { connect } from 'react-redux';
import { DuxTable } from 'duxtable';
import { ViewBroadcast } from '../ViewBroadcast.jsx';

class MyBroadcastsUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            showViewBroadcast: false,
            viewSentBy: '',
            viewDelivered: '',
            viewShortMsg: '',
            viewLongMsg: '',
            viewAttachmentUrl: ''
        };
    }

    componentDidMount() {
        this.props.init();
    }

    selectedRowRender = (item, colNum) => {
        if (colNum === 2) {
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
            viewDelivered: item.Sent,
            viewShortMsg: item.ShortMsg,
            viewLongMsg: item.LongMsg,
            viewAttachmentUrl: item.AttachmentUrl
        });
    };

    render() {
        const columns = [
            {
                title: 'Delivered',
                field: 'Sent',
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
                <DuxTable name="mybroadcasts"
                          columns={columns}
                          data={this.props.broadcasts}
                          sortColumn={0}
                          sortAscending={false}
                          rowKey="BroadcastId"
                          striped={true}
                          selectionMode="single"
                          selectedRenderCallback={this.selectedRowRender}
                />

                <ViewBroadcast show={this.state.showViewBroadcast}
                               sentBy={this.state.viewSentBy}
                               delivered={this.state.viewDelivered}
                               shortMsg={this.state.viewShortMsg}
                               longMsg={this.state.viewLongMsg}
                               attachmentUrl={this.state.viewAttachmentUrl}
                               closeClicked={() => this.setState({showViewBroadcast:false})}
                />
            </div>
        );
    }
}

MyBroadcastsUi.propTypes = {
    fetching: PropTypes.bool.isRequired,
    broadcasts: PropTypes.array.isRequired,

    init: PropTypes.func.isRequired
};

const Container = connect(mapMyBroadcastsProps, mapMyBroadcastsDispatch)(MyBroadcastsUi);

export default Container;
