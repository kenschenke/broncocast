import React from 'react';
import PropTypes from 'prop-types';
import { mapAdminProps, mapAdminDispatch } from '../maps/Routes/Admin.map';
import { connect } from 'react-redux';

class AdminUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        return (
            <div>
                <h1>Admin</h1>
            </div>
        );
    }
}

AdminUi.propTypes = {
    init: PropTypes.func.isRequired
};

const Container = connect(mapAdminProps, mapAdminDispatch)(AdminUi);

export default Container;
