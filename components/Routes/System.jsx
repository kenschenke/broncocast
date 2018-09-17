import React from 'react';
import PropTypes from 'prop-types';
import { mapSystemProps, mapSystemDispatch } from '../maps/Routes/System.map';
import { connect } from 'react-redux';

class SystemUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        return (
            <div>
                <h1>System</h1>
            </div>
        );
    }
}

SystemUi.propTypes = {
    init: PropTypes.func.isRequired
};

const Container = connect(mapSystemProps, mapSystemDispatch)(SystemUi);

export default Container;
