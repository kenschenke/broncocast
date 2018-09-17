import React from 'react';
import PropTypes from 'prop-types';
import { mapMyBroadcastsProps, mapMyBroadcastsDispatch } from '../maps/Routes/MyBroadcasts.map';
import { connect } from 'react-redux';

class MyBroadcastsUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        return (
            <div>
                <h1>My Broadcasts</h1>
            </div>
        );
    }
}

MyBroadcastsUi.propTypes = {
    init: PropTypes.func.isRequired
};

const Container = connect(mapMyBroadcastsProps, mapMyBroadcastsDispatch)(MyBroadcastsUi);

export default Container;
